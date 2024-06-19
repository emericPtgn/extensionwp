jQuery(document).ready(function($) {
    $('.fetch_open_food_facts_single').on('click', function() {
        console.log("Button clicked!"); // Vérifiez que l'événement click est déclenché

        // Récupérer le parent <tr> de l'élément cliqué
        var parentTr = $(this).closest('tr');

        // Récupérer la valeur de l'attribut data-colname="UGS" du parent <tr>
        var ugs = parentTr.find('td[data-colname="UGS"]').text().trim();
        console.log("UGS value:", ugs); // Débogage pour vérifier la valeur de UGS

        if (ugs) {
            fetchdata(ugs);
        } else {
            var sku = $('#_sku').val(); // Récupérer la valeur du champ SKU
            console.log("SKU value:", sku); // Débogage pour vérifier la valeur de SKU
            fetchdata(sku); // Appeler la fonction fetchdata avec le SKU récupéré
        }
    });

    async function fetchdata(sku) {
        const url = `https://world.openfoodfacts.org/api/v0/product/${sku}.json`;

        try {
            const response = await fetch(url);

            if (response.ok) {
                const data = await response.json();
                console.log('Data:', data);

                if (data.status === 0) {
                    console.error('Erreur : ' + data.status_verbose);
                    return;
                }

                const productData = {
                    productId: data.code,
                    nutriscoreGrade: data.product.nutriscore_grade || 'N/A',
                    imageUrl: data.product.image_url || 'N/A',
                    keywords: data.product.categories_tags || [],
                };

                console.log('Product Data:', productData);

                // Envoyer les données via AJAX vers le backend WP
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
});
