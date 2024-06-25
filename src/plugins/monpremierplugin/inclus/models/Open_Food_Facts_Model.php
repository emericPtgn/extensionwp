<?php

require_once plugin_dir_path(__FILE__) . '../models/Database.php';

class Open_Food_Facts_Model {

    private Database $database;

    public function __construct(Database $database){
        $this->database = $database;      
    }

    public static function save_product_data($product_data) {
        global $wpdb;
    
        // Vérifier que toutes les données nécessaires sont présentes
        if (empty($product_data['nutriscoreGrade']) || empty($product_data['barecode']) || empty($product_data['imageUrl']) || empty($product_data['keywords'])) {
            error_log('Tentative d\'enregistrement de données de produit incomplètes.');
            return new WP_Error('incomplete_data', 'Données de produit incomplètes.');
        }
    
        $table_name = $wpdb->prefix . 'open_food_facts';
        $barecode = $product_data['barecode'];
    
        // Vérifier si le produit existe déjà en base de données
        $existing_product = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE barecode = %s", $barecode)
        );
    
        if ($existing_product) {
            // Mettre à jour les données existantes
            $data = array(
                'nutriscore' => $product_data['nutriscoreGrade'],
                'image_url' => $product_data['imageUrl'],
                'keywords' => json_encode($product_data['keywords']), // Convertir en JSON pour stockage
                'last_maj' => current_time('mysql')
            );
    
            $where = array('barecode' => $barecode);
            $format = array('%s', '%s', '%s');
    
            $result = $wpdb->update($table_name, $data, $where, $format);
    
            if ($result === false) {
                error_log("Échec de la mise à jour des données pour le produit avec le codebare $barecode : " . $wpdb->last_error);
                return new WP_Error('db_error', 'Erreur lors de la mise à jour des données.');
            }
    
            return array('action' => 'update', 'id' => $existing_product->id);
        } else {
            // Insérer un nouveau produit
            $data = array(
                'nutriscore' => $product_data['nutriscoreGrade'],
                'barecode' => $product_data['barecode'],
                'image_url' => $product_data['imageUrl'],
                'keywords' => json_encode($product_data['keywords']), // Convertir en JSON pour stockage
                'last_maj' => current_time('mysql')
            );
    
            $format = array('%s', '%s', '%s', '%s');
    
            $result = $wpdb->insert($table_name, $data, $format);
    
            if ($result === false) {
                error_log("Échec de l'insertion des données dans $table_name: " . $wpdb->last_error);
                return new WP_Error('db_error', 'Erreur lors de l\'insertion des données.');
            }
    
            return array('action' => 'insert', 'id' => $wpdb->insert_id);
        }
    }

    public static function get_open_food_facts_from_sku($sku){
        $log_file = plugin_dir_path(__FILE__) . '.././log/model/log.txt';
        file_put_contents($log_file, ['sku' => $sku], FILE_APPEND);

        global $wpdb;
        $tableName = $wpdb->prefix . 'open_food_facts';
        $query = $wpdb->prepare(
            "SELECT nutriscore, keywords, last_maj 
             FROM $tableName 
             WHERE barecode = %s",
            $sku
        );
        $result = $wpdb->get_row($query, ARRAY_A);
        if($result === null){
            file_put_contents($log_file, ['result' => $result . 'result get_row'], FILE_APPEND);
            return 'erreur dans la récupération des données nutriscore, etc.';
        } else {
            return $result;
        }
    }
    

    
}
