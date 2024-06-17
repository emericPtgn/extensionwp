<?php
/*
Plugin Name: my-first-plugin
Description: Plugin to add Open Food Facts button on WooCommerce product admin pages.
Version: 1.0
Author: Your Name
*/

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . '/inclus/controller/open_food_facts_controller.php';

// Initialize the controller
add_action('plugins_loaded', ['Open_Food_Facts_Controller', 'init']);
