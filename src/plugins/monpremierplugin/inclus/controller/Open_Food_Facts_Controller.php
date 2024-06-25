<?php
// Inclus le fichier de la classe Open_Food_Facts_Model
require_once plugin_dir_path(__FILE__) . '../models/Open_Food_Facts_Model.php';

use Symfony\Component\HttpClient\HttpClient;

class Open_Food_Facts_Controller {

    public static function init() {
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_open_food_facts_script']);
        add_action('wp_ajax_fetch_open_food_facts', [__CLASS__, 'fetch_open_food_facts']);

        // Pour la page de liste des produits
        add_filter('manage_edit-product_columns', [__CLASS__, 'add_open_food_facts_column']);
        add_action('manage_product_posts_custom_column', [__CLASS__, 'render_open_food_facts_column'], 10, 2);

        // Ajout des méta boxes
        add_action('add_meta_boxes', [__CLASS__, 'add_open_food_facts_button']);
        add_action('add_meta_boxes', [__CLASS__, 'add_open_food_facts_fields']);

        // Appel de la fonction pour charger les données Open Food Facts uniquement lors de l'édition de produit
        add_action('load-post.php', [__CLASS__, 'load_edit_product_page']);
        add_action('load-post-new.php', [__CLASS__, 'load_edit_product_page']);

        // Appel de la fonction pour enregistrer une méta donnée lorsque l'utilisateur ajoute un nouveau produit
        add_action('woocommerce_new_product', [__CLASS__, 'add_default_meta_on_product_creation'], 10, 1);

        // Attacher la fonction à l'événement 'update_isactiv_meta'
        add_action('update_isactiv_meta', [__CLASS__, 'update_isactiv_meta']);

        // Planifier l'événement récurrent de test
        if (!wp_next_scheduled('test_new_event')) {
            wp_schedule_event(time(), 'every_second', 'test_new_event');
        }
        // Attacher la fonction à l'événement de test
        add_action('test_new_event', [__CLASS__, 'test_new_event']);
        // Ajouter le filtre pour les intervalles de cron personnalisés
        add_filter('cron_schedules', ['Open_Food_Facts_Controller', 'add_cron_intervals']);
    }


    public static function write_log($message, $file) {
        $current_time = date("Y-m-d H:i:s");
        $log_message = '[' . $current_time . '] ' . $message . PHP_EOL;
        file_put_contents($file, $log_message, FILE_APPEND);
    }
    
    public static function add_default_meta_on_product_creation($post_id) {
        // Ajoutez la méta-donnée avec la valeur par défaut 'true'
        update_post_meta($post_id, '_isActiv', true);
        $meta = get_post_meta($post_id, '_isActiv', true);

        // Journaliser l'ajout de la méta-donnée
        $log_file = plugin_dir_path(__FILE__) . '../log/controller/log.txt';
        self::write_log('New product created. Meta data added for product ID: ' . $post_id, $log_file);
        self::write_log('Meta: ' . $meta, $log_file);

    }

    public static function load_edit_product_page() {
        $screen = get_current_screen();
        if ($screen->id === 'product') {
            add_action('edit_form_after_title', [__CLASS__, 'display_open_food_facts_product_datas']);
        }
    }

    public static function add_open_food_facts_fields() {
        add_meta_box(
            'open_food_facts_fields',
            'Open Food Facts Informations',
            [__CLASS__, 'render_open_food_facts_fields'],
            'product',
            'advanced',
            'high'
        );
    }

    public static function render_open_food_facts_fields() {
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
        $postId = $_GET['post'];
        $is_active = get_post_meta($postId, '_isActiv', true);
        $button_state = ($is_active == '1') ? 'Enable' : 'Disabled';
        $log_file = plugin_dir_path(__FILE__) . '../log/controller/log.txt';
        self::write_log('postId: ' . $postId, $log_file);
        self::write_log('isActiv: ' . $is_active, $log_file);
        self::write_log('buttonDisabled: ' . $button_state, $log_file);
        require plugin_dir_path(__FILE__) . '../vue/open-food-facts-view.php';
    }

    public static function add_open_food_facts_column($columns) {
        $columns['open_food_facts'] = 'Open Food Facts';
        return $columns;
    }

    public static function render_open_food_facts_column($column, $post_id) {
        $is_active = get_post_meta($post_id, '_isActiv', true);
        $log_file = plugin_dir_path(__FILE__) . '../log/controller/log.txt';
    
        // Ajout de logs pour voir combien de fois la fonction est appelée
        error_log('render_open_food_facts_column called for post_id: ' . $post_id);
        self::write_log('render_open_food_facts_column called for post_id: ' . $post_id, $log_file);
        self::write_log('column: ' . $column, $log_file);
        self::write_log(' ceci est un message ' . var_export($is_active, true), $log_file);
    
        if ($column === 'open_food_facts') {
            // Vérifiez la valeur de la méta donnée et ajustez l'état du bouton
            $button_state = ($is_active == '1') ? 'Enable' : 'Disabled';
    
            // Passez l'état du bouton à la vue
            require plugin_dir_path(__FILE__) . '../vue/open-food-facts-view.php';
        }
    }
    

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

    // Fonction pour récupérer les données de Open Food Facts et planifier un événement
    public static function fetch_open_food_facts() {
        check_ajax_referer('open_food_facts_nonce', 'nonce');

        try {
            if (isset($_POST['productData'])) {
                $product_data = is_array($_POST['productData']) ? $_POST['productData'] : json_decode(stripslashes($_POST['productData']), true);

                // Vérifier que toutes les données nécessaires sont présentes
                if (empty($product_data['nutriscoreGrade']) || empty($product_data['barecode']) || empty($product_data['imageUrl']) || empty($product_data['keywords']) || empty($product_data['productId'])) {
                    throw new Exception('Données de produit incomplètes.');
                }
                $productId = $product_data['productId'];
                $result = Open_Food_Facts_Model::save_product_data($product_data);

                if (is_wp_error($result)) {
                    wp_send_json_error($result->get_error_message());
                } else {
                    $response = array(
                        'success' => true,
                        'data' => 'Données enregistrées avec succès.'
                    );

                    if (isset($result['action'])) {
                        $log_file = plugin_dir_path(__FILE__) . '../log/controller/log.txt';
                        update_post_meta($productId, '_isActiv', "0");
                        $meta = get_post_meta($productId, '_isActiv', true);
                        error_log('MetaIsActiv: ' . var_export($meta, true));
                        self::write_log('Meta when update post meta is false: ' . $meta, $log_file);
                        if ($result['action'] === 'insert') {
                            $response['data'] = 'Produit ajouté avec succès.';
                        } elseif ($result['action'] === 'update') {
                            $response['data'] = 'Données mises à jour avec succès.';
                        }

                        // Planifier l'événement pour mettre à jour la méta donnée après 72 heures
                        if (!wp_next_scheduled('update_isactiv_meta', array($productId))) {
                            wp_schedule_single_event(time() + 72 * 60 * 60, 'update_isactiv_meta', array($productId));
                        }
                    }

                    wp_send_json_success($response);
                }
            } else {
                throw new Exception('Données du produit manquantes.');
            }
        } catch (Exception $e) {
            error_log('Erreur lors de la récupération des données : ' . $e->getMessage());
            wp_send_json_error('Erreur lors de la récupération des données : ' . $e->getMessage());
        }
    }

    // Fonction pour mettre à jour la méta donnée _isActiv après 72 heures
    public static function update_isactiv_meta($productId) {
        update_post_meta($productId, '_isActiv', '1');
        error_log('Mise à jour de la méta donnée _isActiv pour le produit ID: ' . $productId);
    }

    // Fonction pour nettoyer les événements planifiés si nécessaire
    public static function clear_scheduled_isactiv_event($productId) {
        $timestamp = wp_next_scheduled('update_isactiv_meta', array($productId));
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'update_isactiv_meta', array($productId));
        }
    }
    
    public static function test_new_event() {
        $log_file = plugin_dir_path(__FILE__) . '../log/controller/test_log.txt';
        $message = 'Cron event executed at ' . date('Y-m-d H:i:s') . "\n";
        file_put_contents($log_file, $message, FILE_APPEND);
    }

    // Ajouter l'intervalle 'every_minute' pour les tests
    public static function add_cron_intervals($schedules) {
        $schedules['every_minute'] = array(
            'interval' => 60,
            'display' => __('Chaque Minute')
        );
        return $schedules;
    }

    public static function display_open_food_facts_product_datas() {
        $log_file = plugin_dir_path(__FILE__) . '../log/controller/log.txt';
    
        // Fonction de journalisation
        function write_log($message, $file) {
            $current_time = date("Y-m-d H:i:s");
            $log_message = '[' . $current_time . '] ' . print_r($message, true) . PHP_EOL;
            file_put_contents($file, $log_message, FILE_APPEND);
        }
    
        // Récupérer l'ID du produit
        $product_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        write_log('Product ID: ' . $product_id, $log_file);
    
        // Récupérer les clés API
        $consumerKey = $_ENV['CONSUMER_KEY'] ?? 'your_consumer_key';
        write_log('consumerKey: ' . $consumerKey, $log_file);
        $consumerSecret = $_ENV['CONSUMER_SECRET'] ?? 'your_consumer_secret';
        write_log('consumerSecret: ' . $consumerSecret, $log_file);
        $ip = $_ENV['IP'];
        // Construire l'URL de l'API sans afficher l'ip
        $url = 'https://'.$ip.'/wp-json/wc/v3/products/' . $product_id;
    
        try {
            // Créer une instance du client HTTP
            $client = HttpClient::create([
                'verify_peer' => false, // pour ignorer les erreurs de certificat auto-signé
                'verify_host' => false,
                'base_uri' => 'https://localhost'
            ]);
    
            // Options pour l'authentification de base
            $options = [
                'auth_basic' => [
                    'Username' => $consumerKey,
                    'Password' => $consumerSecret,
                ]
            ];
    
            // Effectuer la requête GET
            $response = $client->request('GET', $url, $options);
    
            // Vérifier le code de statut HTTP
            if ($response->getStatusCode() !== 200) {
                write_log('HTTP Error: ' . $response->getStatusCode(), $log_file);
                return;
            }
    
            // Récupérer le corps de la réponse et le décoder en tableau PHP
            $body = $response->getContent();
            $data = json_decode($body, true);
            write_log('Response Data: ' . $body, $log_file);
    
            // Vérifier si le SKU existe dans les données de réponse
            if (isset($data['sku'])) {
                $sku = $data['sku'];
                write_log('SKU: ' . $sku, $log_file);
            } else {
                write_log('SKU not found in response data', $log_file);
                return;
            }
    
            // Récupérer les valeurs Open Food Facts à afficher
            $valuesToDisplay = Open_Food_Facts_Model::get_open_food_facts_from_sku($sku);
            write_log('Values to Display: ' . print_r($valuesToDisplay, true), $log_file);
    
            // Enregistrer les valeurs à afficher dans une variable globale ou dans la meta du produit
            $GLOBALS['open_food_facts_data'] = $valuesToDisplay;
    
        } catch (\Exception $e) {
            write_log('HTTP Client Error: ' . $e->getMessage(), $log_file);
        }
    }
}

Open_Food_Facts_Controller::init();

