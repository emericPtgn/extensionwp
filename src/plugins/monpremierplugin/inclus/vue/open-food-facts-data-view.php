<!-- open-food-facts-data-view.php -->

<div id="open_food_facts_data_section">
    <?php if (isset($GLOBALS['open_food_facts_data']) && is_array($GLOBALS['open_food_facts_data']) && !empty($GLOBALS['open_food_facts_data'])) : ?>
        <div>
            <label>nutriscore</label>
            <input type="text" class="large-text" value="<?php echo esc_attr($GLOBALS['open_food_facts_data']['nutriscore']); ?>" />
        </div>
        <div>
            <label>keywords</label>
            <input type="text" class="large-text" value="<?php echo esc_attr($GLOBALS['open_food_facts_data']['keywords']); ?>" />
        </div>
        <div>
            <span>Dernière modification</span>
            <b><?php echo esc_html($GLOBALS['open_food_facts_data']['last_maj']); ?></b>
        </div>
        <div>
            <ul>
                <li>
                    <img src="<?php echo esc_url($data['image_url']); ?>" alt="Product Image" width="100">
                </li>
            </ul>
        </div>
    <?php else : ?>
        <p>Erreur dans la récupération des données ou données non trouvées.</p>
    <?php endif; ?>

</div>
