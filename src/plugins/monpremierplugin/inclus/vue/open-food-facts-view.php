<?php 
// Variable $button_state doit être accessible ici
?>
<div id="open_food_facts_data_single">
    <button 
        id="fetch_open_food_facts_single" 
        class="button fetch_open_food_facts_single" 
        type="button"
        <?php echo $button_state === 'Disabled' ? 'disabled' : ''; ?>
    >
        Fetch Open Food Facts Data
    </button>
</div>
