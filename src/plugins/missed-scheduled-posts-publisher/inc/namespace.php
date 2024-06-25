<?php

namespace WPB\MissedScheduledPostsPublisher;

const ACTION              = 'wpb_missed_scheduled_posts_publisher';
const BATCH_LIMIT         = 20;
const FALLBACK_MULTIPLIER = 1.1;
const OPTION_NAME         = 'wpb-missed-scheduled-posts-publisher-last-run';


/**
 * Filters the frequency to allow programmaticaly control.
 * 
 * @param int $frequency The frequency in seconds.
 */
function get_run_frequency() {
	$frequency = 900;
	return (int) apply_filters( 'wpb_missed_scheduled_posts_publisher_frequency', $frequency );
}

function bootstrap() {
	add_action( 'send_headers', __NAMESPACE__ . '\\send_headers' );
	add_action( 'shutdown', __NAMESPACE__ . '\\loopback' );
	add_action( 'wp_ajax_nopriv_' . ACTION, __NAMESPACE__ . '\\admin_ajax' );
	add_action( 'wp_ajax_' . ACTION, __NAMESPACE__ . '\\admin_ajax' );
}

/**
 * Generate a nonce without the UID and session components.
 *
 * As this is a loopback request, the user will not be registered as logged in
 * so the generic WP Nonce function will not work.
 *
 * @return string Nonce based on action name and tick.
 */
function get_no_priv_nonce() {
	$uid   = 'n/a';
	$token = 'n/a';
	$i     = wp_nonce_tick();

	return substr( wp_hash( $i . '|' . ACTION . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
}

/**
 * Verify a nonce without the UID and session components.
 *
 * As this comes from a loopback request, the user will not be registered as
 * logged in so the generic WP Nonce function will not work.
 *
 * The goal here is to mainly to protect against database reads in the event
 * of both full page caching and falling back to the ajax request in place of
 * a successful loopback request.
 *
 * @param string $nonce Nonce based on action name and tick.
 * @return false|int False if nonce invalid. Integer containing tick if valid.
 */
function verify_no_priv_nonce( $nonce ) {
	$nonce = (string) $nonce;

	if ( empty( $nonce ) ) {
		return false;
	}

	$uid   = 'n/a';
	$token = 'n/a';
	$i     = wp_nonce_tick();

	// Nonce generated 0-12 hours ago.
	$expected = substr( wp_hash( $i . '|' . ACTION . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
	if ( hash_equals( $expected, $nonce ) ) {
		return 1;
	}

	// Nonce generated 12-24 hours ago.
	$expected = substr( wp_hash( ( $i - 1 ) . '|' . ACTION . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
	if ( hash_equals( $expected, $nonce ) ) {
		return 2;
	}

	return false;
}

/**
 * Prevent caching of requests including the AJAX script.
 *
 * Includes the no-caching headers if the response will include the
 * AJAX fallback script. This is to prevent excess calls to the
 * admin-ajax.php action.
 */
function send_headers() {
	$last_run = (int) get_option( OPTION_NAME, 0 );
	if ( $last_run >= ( time() - ( FALLBACK_MULTIPLIER * get_run_frequency() ) ) ) {
		return;
	}

	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_scripts' );
	nocache_headers();
}

/**
 * Enqueue inline AJAX request to allow for failing loopback requests.
 */
function enqueue_scripts() {
	$last_run = (int) get_option( OPTION_NAME, 0 );
	if ( $last_run >= ( time() - ( FALLBACK_MULTIPLIER * get_run_frequency() ) ) ) {
		return;
	}

	// Shutdown loopback request is not needed.
	remove_action( 'shutdown', __NAMESPACE__ . '\\loopback' );

	// Null script for inline script to come afterward.
	wp_register_script(
		ACTION,
		null,
		array(),
		null,
		true
	);

	$request = array(
		'url'  => add_query_arg( 'action', ACTION, admin_url( 'admin-ajax.php' ) ),
		'args' => array(
			'method' => 'POST',
			'body'   => ACTION . '_nonce=' . get_no_priv_nonce(),
		),
	);

	$script = '
	(function( request ){
		if ( ! window.fetch ) {
			return;
		}
		request.args.body = new URLSearchParams( request.args.body );
		fetch( request.url, request.args );
	}( ' . wp_json_encode( $request ) . ' ));
	';

	wp_add_inline_script(
		ACTION,
		$script
	);

	wp_enqueue_script( ACTION );
}

/**
 * Make a loopback request to publish posts with a missed schedule.
 */
function loopback() {
	$last_run = (int) get_option( OPTION_NAME, 0 );
	if ( $last_run >= ( time() - get_run_frequency() ) ) {
		return;
	}

	// Do loopback request.
	$request = array(
		'url'  => add_query_arg( 'action', ACTION, admin_url( 'admin-ajax.php' ) ),
		'args' => array(
			'timeout'   => 0.01,
			'blocking'  => false,
			/** This filter is documented in wp-includes/class-wp-http-streams.php */
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			'body'      => array(
				ACTION . '_nonce' => get_no_priv_nonce(),
			),
		),
	);

	wp_remote_post( $request['url'], $request['args'] );
}

/**
 * Handle HTTP request for publishing posts with a missed schedule.
 *
 * Always response with a success result to allow for full page caching
 * retaining the inline script. The visitor does not need to see error
 * messages in their browser.
 */
function admin_ajax() {
	if ( ! verify_no_priv_nonce( $_POST[ ACTION . '_nonce' ] ) ) {
		wp_send_json_success();
	}

	$last_run = (int) get_option( OPTION_NAME, 0 );
	if ( $last_run >= ( time() - get_run_frequency() ) ) {
		wp_send_json_success();
	}

	publish_missed_posts();
	wp_send_json_success();
}

/**
 * Publish posts with a missed schedule.
 */
function publish_missed_posts() {
	global $wpdb;

	update_option( OPTION_NAME, time() );

	$scheduled_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_date <= %s AND post_status = 'future' LIMIT %d",
			current_time( 'mysql', 0 ),
			BATCH_LIMIT
		)
	);
	if ( ! count( $scheduled_ids ) ) {
		return;
	}
	if ( count( $scheduled_ids ) === BATCH_LIMIT ) {
		// There's a bit to do.
		update_option( OPTION_NAME, 0 );
	}

	array_map( 'wp_publish_post', $scheduled_ids );
}
