    <?php
    function off_admin_page_content() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'open_food_facts';
        $products = $wpdb->get_results("SELECT * FROM $table_name");

        echo '<div class="wrap">';
        echo '<h1>Open Food Facts</h1>';
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
            echo '<td>';
            echo '<form method="post">';
            echo '<input type="hidden" name="product_id" value="' . esc_attr($product->product_id) . '">';
            echo '<button type="submit" name="off_update_product" class="button-primary">Mettre à jour</button>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }

    // Hook pour ajouter le contenu à une page d'administration
    add_action('admin_menu', 'off_admin_menu');
    function off_admin_menu(){
        add_menu_page(
            'Open FF', // Titre de la page
            'Open FF', // Texte à afficher dans le lien du menu
            'manage_options', // Capabilité requise pour voir le lien
            'openff-page', // Slug pour faire référence à la page
            'off_admin_page_content' // Fonction pour afficher le contenu de la page
        );
    }