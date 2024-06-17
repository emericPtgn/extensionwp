jQuery(document).ready(function($) {
    $('#fetch_open_food_facts_single').on('click', function() {
        console.log('Button clicked');
        var barcode = $(this).data('barcode');
        console.log('Barcode:', barcode);
        fetchdata(barcode);
    });
});

async function fetchdata(barcode) {
    const url = `https://world.openfoodfacts.org/api/v0/product/${barcode}.json`;

    try {
        const response = await fetch(url);

        if (response.ok) {
            console.log('Response ok !!');
            const data = await response.json();
            console.log('Data:', data);

            const productData = {
                productId: data.code,
                nutriscoreGrade: data.product.nutriscore_grade,
                imageUrl: data.product.image_url,
                keywords: data.product.categories_tags,
            };

            console.log('Product Data:', productData);

            // Utilisation de jQuery pour envoyer les données via AJAX vers le backend WP
            jQuery.ajax({
                url: openFoodFacts.ajax_url,
                type: 'POST',
                data: {
                    action: 'fetch_open_food_facts',
                    productData: productData,
                    nonce: openFoodFacts.nonce
                },
                success: function(response) {
                    if (response.success) {
                        console.log(response.data); // Afficher le message renvoyé par le modèle
                    } else {
                        console.log('Erreur : ' + response.data); // Afficher le message d'erreur renvoyé par le modèle
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erreur AJAX :', error);
                }
            });
        } else {
            console.log('Response NOT OK !');
        }
    } catch (error) {
        console.error('Erreur lors de la récupération des données :', error);
    }
}