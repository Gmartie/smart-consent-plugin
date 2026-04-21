
document.addEventListener('click', function(e) {

    // Botón añadir al carrito de WooCommerce
    if (e.target.classList.contains('add_to_cart_button')) {

        // Leer el precio real del botón si está disponible
        const price = parseFloat(e.target.dataset.price) || 0;
        const productId = e.target.dataset.productId || '';
        const productName = e.target.closest('.product')?.querySelector('.woocommerce-loop-product__title')?.innerText || '';

        trackEvent('add_to_cart', {
            currency: 'EUR',
            value: price,
            items: [{
                item_id: productId,
                item_name: productName,
                price: price,
                quantity: 1
            }]
        });
    }

});
