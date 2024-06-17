<?php
/*
Plugin Name: Open FF
Description: Module pour mettre à jour fiches produits via appel à l'api open food facts
Author: Emeric Petitgenet
Version: 1.0
*/

// Hook pour ajouter le plugin au menu
add_action('admin_menu', 'off_admin_menu');
function off_admin_menu(){
    add_menu_page(
        'Open ff', // Title of the page
        'Open ff', // Text to show on the menu link
        'manage_options', // Capability requirement to see the link
        'openff-page', // Slug to refer to the page
        'off_admin_page' // Function to display the page content
    );
    error_log("Menu page added.");
}

// Définir le contenu de la page
function off_admin_page(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'open_food_facts';
    $products = $wpdb->get_results("SELECT * FROM $table_name");

    if (!$products) {
        error_log("No products found in $table_name.");
    }

    echo '<div class="wrap">';
    echo '<h1>Open Food Facts</h1>';
    echo '<button type="submit" name="off_update_product" class="button-primary">Mettre à jour</button>';
    echo '<table class="widefat">';
    echo '<thead><tr><th>ID</th><th>Product ID</th><th>Image URL</th><th>Keywords</th><th>Last Update</th><th>Actions</th></tr></thead>';
    echo '<tbody>';

    foreach ($products as $product) {
        echo '<tr>';
        echo '<td>' . esc_html($product->id) . '</td>';
        echo '<td>' . esc_html($product->product_id) . '</td>';
        echo '<td><img src="' . esc_url($product->image_url) . '" width="50"></td>';
        echo '<td>' . esc_html($product->keywords) . '</td>';
        echo '<td>' . esc_html($product->last_maj) . '</td>';
        echo '<td><button class="off-update-button" data-product-id="' . esc_attr($product->product_id) . '">Mettre à jour</button></td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';
}

// Renvoyer les infos produits à partir du code barre
function off_get_product_data($barcode){
    $response = wp_remote_get('https://world.openfoodfacts.org/api/v0/product/' . $barcode . '.json');
    if (is_wp_error($response)) {
        error_log("Error retrieving product data: " . $response->get_error_message());
        return false;
    }
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body);
    return $data;
}

// Mettre à jour la fiche produit à partir d'un id produit
function off_update_product_data($product_id){
    global $wpdb;
    $table_name = $wpdb->prefix . 'open_food_facts';
    $data = off_get_product_data($product_id);
    if ($data && isset($data->product)) {
        $wpdb->replace($table_name, [
            'product_id' => $product_id,
            'image_url' => $data->product->image_url,
            'keywords' => implode(',', $data->product->keywords_tags),
            'last_maj' => current_time('mysql')
        ]);
        error_log("Product data updated for product_id $product_id.");
    } else {
        error_log("Failed to update product data for product_id $product_id.");
    }
}

// Action hook déclenche callback si requête ajax update_product
add_action('wp_ajax_update_product', 'off_handle_update_product');

function off_handle_update_product(){
    if (!isset($_POST['product_id']) || !current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized', 403);
        error_log("Unauthorized request for updating product.");
    }

    $product_id = intval($_POST['product_id']);
    off_update_product_data($product_id);
    wp_send_json_success('Product updated');
    error_log("Product updated via AJAX for product_id $product_id.");
}

// Enfile le JavaScript
add_action('admin_enqueue_scripts', 'off_enqueue_admin_scripts');

function off_enqueue_admin_scripts($hook) {
    if ($hook !== 'toplevel_page_openff-page') {
        return;
    }

    wp_enqueue_script('off-admin-js', plugin_dir_url(__FILE__) . 'js/admin.js', ['jquery'], null, true);
    wp_localize_script('off-admin-js', 'off_ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);
    error_log("Admin script enqueued.");
}

