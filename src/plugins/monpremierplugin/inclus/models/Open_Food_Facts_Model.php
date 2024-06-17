<?php

class Open_Food_Facts_Model {

    public static function save_product_data($product_data) {
        global $wpdb;
    
        // Vérifier que toutes les données nécessaires sont présentes
        if (empty($product_data['nutriscoreGrade']) || empty($product_data['productId']) || empty($product_data['imageUrl']) || empty($product_data['keywords'])) {
            error_log('Tentative d\'enregistrement de données de produit incomplètes.');
            return new WP_Error('incomplete_data', 'Données de produit incomplètes.');
        }
    
        $table_name = $wpdb->prefix . 'open_food_facts';
        $product_id = $product_data['productId'];
    
        // Vérifier si le produit existe déjà en base de données
        $existing_product = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE product_id = %s", $product_id)
        );
    
        if ($existing_product) {
            // Mettre à jour les données existantes
            $data = array(
                'nutriscore' => $product_data['nutriscoreGrade'],
                'image_url' => $product_data['imageUrl'],
                'keywords' => json_encode($product_data['keywords']), // Convertir en JSON pour stockage
                'last_maj' => current_time('mysql')
            );
    
            $where = array('product_id' => $product_id);
            $format = array('%s', '%s', '%s');
    
            $result = $wpdb->update($table_name, $data, $where, $format);
    
            if ($result === false) {
                error_log("Échec de la mise à jour des données pour le produit avec l'ID $product_id : " . $wpdb->last_error);
                return new WP_Error('db_error', 'Erreur lors de la mise à jour des données.');
            }
    
            return array('action' => 'update', 'id' => $existing_product->id);
        } else {
            // Insérer un nouveau produit
            $data = array(
                'nutriscore' => $product_data['nutriscoreGrade'],
                'product_id' => $product_data['productId'],
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
    
}
