<?php
/*
 * Add my new menu to the Admin Control Panel
 */
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}
// Hook the 'admin_menu' action hook, run the function named 'mfp_Add_My_Admin_Link()'
add_action( 'admin_menu', 'mfp_Add_My_Admin_Link' );
 
// Add a new top level menu link to the ACP
function mfp_Add_My_Admin_Link()
{
      add_menu_page(
        'My First Page', // Title of the page
        'Mon plugin', // Text to show on the menu link
        'manage_options', // Capability requirement to see the link
        '/var/www/html/wp-content/plugins/monpremierplugin/inclus/vue/mfp-first-acp-page.php',
    );
};