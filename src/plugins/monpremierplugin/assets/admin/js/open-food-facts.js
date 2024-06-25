jQuery(document).ready(function($) {
    $('.fetch_open_food_facts_single').on('click', function() {
        console.log("Bouton cliqué!");

        // Récupérer le parent <tr> de l'élément cliqué
        var parentTr = $(this).closest('tr');

        // Récupérer la valeur de l'attribut data-colname="UGS" du parent <tr>
        var ugs = parentTr.find('td[data-colname="UGS"]').text().trim();
        if(ugs){
            var rawProductId = parentTr.find('td[data-colname="Nom"] .row-actions .id').text().trim();
            var productId = rawProductId.match(/\d+/)[0];
            console.log("Product ID:", productId);
            console.log("UGS value:", ugs); 
        }

        // Récupérer l'ID de produit depuis l'URL
        var urlParams = new URLSearchParams(window.location.search);
        var productIdFromUrl = urlParams.get('post');
        console.log('Product ID depuis URL:', productIdFromUrl);

        if (ugs) {
            fetchdata(ugs, productId);
        } else {
            var sku = $('#_sku').val(); // Récupérer la valeur du champ SKU
            console.log("Valeur de SKU:", sku); 
            fetchdata(sku, productIdFromUrl); // Appeler la fonction fetchdata avec le SKU récupéré
        }

    });

    async function fetchdata(sku, productId) {
        const url = `https://world.openfoodfacts.org/api/v0/product/${sku}.json`;

        try {
            const response = await fetch(url);

            if (response.ok) {
                const data = await response.json();
                console.log('Données:', data);

                if (data.status === 0) {
                    console.error('Erreur : ' + data.status_verbose);
                    return;
                }

                // Construit l'objet productData avec les données nécessaires
                const productData = {
                    barecode: data.code,
                    nutriscoreGrade: data.product.nutriscore_grade || 'N/A',
                    imageUrl: data.product.image_url || 'N/A',
                    keywords: data.product.categories_tags || [],
                    productId: productId
                };

                console.log('Données du produit:', productData);

                // Envoie les données via AJAX vers le backend WP
                jQuery.ajax({
                    url: openFoodFacts.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'fetch_open_food_facts',
                        productData: productData,
                        nonce: openFoodFacts.nonce
                    },
                    // dataType: 'json', // Assurez-vous de spécifier que la réponse attendue est JSON
                    success: function(response) {
                        if (response.success) {
                            console.log(response.data); // Affiche le message renvoyé par le modèle
                        } else {
                            console.log('Erreur : ' + response.data); // Affiche le message d'erreur renvoyé par le modèle
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Erreur AJAX :', error);
                        console.log('Statut de la réponse:', xhr.status);
                        console.log('Réponse du serveur:', xhr.responseText);

                    }
                });
                
            } else {
                console.log('Réponse NON OK !');
            }
        } catch (error) {
            console.error('Erreur lors de la récupération des données :', error);
        }
    }

});
