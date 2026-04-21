document.addEventListener('click', function(e) {

    // Botón añadir al carrito - producto simple (listado)
    if (e.target.classList.contains('add_to_cart_button')) {
        const price = parseFloat(e.target.dataset.price) || 0;
        const productId = e.target.dataset.productId || '';
        const productName = e.target.closest('.product')
            ?.querySelector('.woocommerce-loop-product__title')?.innerText || '';

        trackEvent('add_to_cart', {
            currency: 'EUR',
            value: price,
            items: [{ item_id: productId, item_name: productName, price, quantity: 1 }]
        });
    }

    // Botón añadir al carrito - producto variable (página de producto)
    if (e.target.classList.contains('single_add_to_cart_button')) {
        const form = e.target.closest('form.cart');
        const productName = document.querySelector('.product_title')?.innerText || '';
        const price = parseFloat(document.querySelector('.woocommerce-Price-amount')?.innerText) || 0;
        const variation = form?.querySelector('input[name="variation_id"]')?.value || '';

        trackEvent('add_to_cart', {
            currency: 'EUR',
            value: price,
            items: [{ item_id: variation, item_name: productName, price, quantity: 1 }]
        });
    }

});