<?php
/* 
Plugin Name: Open FF
Description: module pour mettre à jour fiches produits via appel à l'api open food facts
Author: Emeric Petitgenet
*/

require_once plugin_dir_path(__FILE__) . 'inclus/openff-functions.php';


// Créer la table lorsque le plugin est activé
register_activation_hook(__FILE__, 'off_create_product_data_table');

function off_create_product_data_table(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'open_food_facts';  
    $charset_collate = $wpdb->get_charset_collate();

    // Logging to check if the function is called
    error_log("off_create_product_data_table called.");

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        product_id bigint(20) NOT NULL,
        image_url varchar(255) NOT NULL,
        keywords text NOT NULL,
        last_maj datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY product_id (product_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Check if the table was created
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    if ($table_exists) {
        error_log("Table $table_name created successfully.");
    } else {
        error_log("Failed to create table $table_name.");
    }
}


register_uninstall_hook(__FILE__, 'off_drop_product_data_table');

function off_drop_product_data_table(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'open_food_facts';
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);
    error_log("Table $table_name dropped.");
}
