<!-- open-food-facts-data-view.php -->

<div id="open_food_facts_data_section">
    <h3>Open Food Facts Data</h3>
    <p><strong>Product Id:</strong> <?php echo esc_html($data['product_id']); ?></p>
    <p><strong>Nutriscore:</strong> <?php echo esc_html($data['nutriscore']); ?></p>
    <p><strong>Keywords:</strong> <?php echo esc_html($data['keywords']); ?></p>
    <p><strong>Last Update:</strong> <?php echo esc_html($data['last_maj']); ?></p>
    <img src="<?php echo esc_url($data['image_url']); ?>" alt="Product Image" width="100">
</div>
