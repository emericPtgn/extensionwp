<?php

// Inclus le fichier de la classe Open_Food_Facts_Model
require_once plugin_dir_path(__FILE__) . '../models/Open_Food_Facts_Model.php';

// Utilise la classe Open_Food_Facts_Model
use Open_Food_Facts_Model;


class Open_Food_Facts_Controller {

    public static function init() {
        add_action('add_meta_boxes', [__CLASS__, 'add_open_food_facts_button']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_open_food_facts_script']);
        add_action('wp_ajax_fetch_open_food_facts', [__CLASS__, 'fetch_open_food_facts']);

        // Pour la page de liste des produits
        add_filter('manage_edit-product_columns', [__CLASS__, 'add_open_food_facts_column']);
        add_action('manage_product_posts_custom_column', [__CLASS__, 'render_open_food_facts_column'], 10, 2);

        // Pour la page d'édition de produit
        add_action('add_meta_boxes_product', [__CLASS__, 'add_open_food_facts_button_single']);
        add_action('add_meta_boxes', [__CLASS__, 'add_open_food_facts_fields']);
    }

    public static function add_open_food_facts_fields(){
        add_meta_box(
            'open_food_facts_fields',
            'Open Food Facts Informations',
            [__CLASS__, 'render_open_food_facts_fields'],
            'product',
            'advanced',
            'high'
        );
    }

    public static function render_open_food_facts_fields(){
        require plugin_dir_path(__FILE__) . '../vue/open-food-facts-data-view.php';
    }

    public static function add_open_food_facts_button() {
        add_meta_box(
            'open_food_facts_button',
            'Open Food Facts',
            [__CLASS__, 'render_open_food_facts_button'],
            'product',
            'side',
            'high'
        );
    }

    public static function render_open_food_facts_button($post) {
        require plugin_dir_path(__FILE__) . '../vue/open-food-facts-view.php';
    }

    /**
     * add function to scripts
     */
    public static function enqueue_open_food_facts_script() {
        // Enqueue script only on relevant admin pages
        if (is_admin() && (isset($_GET['post_type']) && $_GET['post_type'] === 'product' || isset($_GET['post']) && get_post_type($_GET['post']) === 'product')) {
            wp_enqueue_script('open_food_facts_script', '/wp-content/plugins/monpremierplugin/assets/admin/js/open-food-facts.js', array('jquery'), null, true);
    
            // Pass PHP variables to JavaScript
            wp_localize_script('open_food_facts_script', 'openFoodFacts', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('open_food_facts_nonce')
            ));
        }
    }
    
    public static function fetch_open_food_facts() {
        check_ajax_referer('open_food_facts_nonce', 'nonce');
    
        if (isset($_POST['productData'])) {
            $product_data = is_array($_POST['productData']) ? $_POST['productData'] : json_decode($_POST['productData'], true);
    
            if ($product_data) {
                $result = Open_Food_Facts_Model::save_product_data($product_data);
    
                if (is_wp_error($result)) {
                    wp_send_json_error($result->get_error_message());
                } else {
                    $response = array(
                        'success' => true,
                        'data' => 'Données enregistrées avec succès.'
                    );
    
                    if (isset($result['action'])) {
                        if ($result['action'] === 'insert') {
                            $response['data'] = 'Produit ajouté avec succès.';
                        } elseif ($result['action'] === 'update') {
                            $response['data'] = 'Données mises à jour avec succès.';
                        }
                    }
    
                    wp_send_json_success($response);
                }
            } else {
                wp_send_json_error('Données du produit invalides.');
            }
        } else {
            wp_send_json_error('Données du produit manquantes.');
        }
    }

    

    public static function add_open_food_facts_column($columns) {
        $columns['open_food_facts'] = 'Open Food Facts';
        return $columns;
    }


    public static function render_open_food_facts_column($column, $post_id) {
        if ($column === 'open_food_facts') {
            echo '<button class="button fetch_open_food_facts" data-barcode="' . esc_attr($post_id) . '">Fetch Open Food Facts</button>';
            echo '<div class="open_food_facts_data" id="open_food_facts_data_' . esc_attr($post_id) . '"></div>';
        }
    }

    public static function add_open_food_facts_button_single($post) {
        add_meta_box(
            'open_food_facts_button',
            'Open Food Facts',
            [__CLASS__, 'render_open_food_facts_button_single'],
            'product',
            'side',
            'high'
        );
    }

    public static function render_open_food_facts_button_single($post) {
        require plugin_dir_path(__FILE__) . '../vue/open-food-facts-view.php';
    }



}
