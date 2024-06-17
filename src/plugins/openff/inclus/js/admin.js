jQuery(document).ready(function($) {
    $('.off-update-button').click(function() {
        var productId = $(this).data('product-id');
        $.ajax({
            url: off_ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'update_product',
                product_id: productId
            },
            success: function(response) {
                if (response.success) {
                    alert('Produit mis à jour');
                    location.reload();
                } else {
                    alert('Erreur lors de la mise à jour');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error: ', error);
                alert('Erreur AJAX');
            }
        });
    });
});
