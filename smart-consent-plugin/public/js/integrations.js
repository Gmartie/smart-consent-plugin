// EVENTO: add_to_cart
// 1. Producto simple en listado (clic en ".add_to_cart_button")
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('add_to_cart_button')) {
        const price       = parseFloat(e.target.dataset.price) || 0;
        const productId   = e.target.dataset.productId || '';
        const productName = e.target.closest('.product')
            ?.querySelector('.woocommerce-loop-product__title')?.innerText || '';

        trackEvent('add_to_cart', {
            currency: 'EUR',
            value: price,
            items: [{ item_id: productId, item_name: productName, price, quantity: 1 }]
        });
    }
});

// 2. Producto variable (y simple) en página de detalle

document.addEventListener('DOMContentLoaded', function() {
    const addBtn = document.querySelector('button.single_add_to_cart_button');
    if (!addBtn) return;

    addBtn.addEventListener('click', function(e) {
        // Ignorar si el botón está deshabilitado (selección de variación automática)
        if (addBtn.classList.contains('disabled') || addBtn.disabled) return;

        const cartForm    = addBtn.closest('form.cart');
        const variationId = cartForm?.querySelector('input[name="variation_id"]')?.value || '';
        const productName = document.querySelector('.product_title')?.innerText || '';

        // Precio: variación activa primero, luego precio general
        const priceEl = document.querySelector(
            '.woocommerce-variation-price .woocommerce-Price-amount bdi, ' +
            '.woocommerce-Price-amount bdi, ' +
            '.woocommerce-Price-amount'
        );
        const price = parseFloat(
            priceEl?.innerText?.replace(/[^\d,.-]/g, '').replace(',', '.') || '0'
        ) || 0;

        const quantity = parseInt(
            cartForm?.querySelector('input[name="quantity"]')?.value, 10
        ) || 1;

        // Atributos seleccionados
        const attributes = {};
        cartForm?.querySelectorAll('select[name^="attribute_"]').forEach(function(sel) {
            if (sel.value) attributes[sel.name.replace('attribute_', '')] = sel.value;
        });

        const itemId = variationId || cartForm?.querySelector('input[name="product_id"]')?.value || '';

        trackEvent('add_to_cart', {
            currency: 'EUR',
            value: price * quantity,
            items: [{
                item_id:    itemId,
                item_name:  productName,
                price:      price,
                quantity:   quantity,
                variation:  variationId || undefined,
                attributes: Object.keys(attributes).length ? attributes : undefined
            }]
        });

        if (smartSettings.debug) {
            console.log('[SmartConsent] add_to_cart (click):', {
                itemId, productName, price, quantity, variationId, attributes
            });
        }
    });
});

// EVENTO: view_item
// Se dispara al cargar cualquier página de producto individual.
document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector('body.single-product')) return;

    const productName = document.querySelector('.product_title')?.innerText || '';
    const priceEl     = document.querySelector('.woocommerce-Price-amount bdi, .woocommerce-Price-amount');
    const price       = parseFloat(
        priceEl?.innerText?.replace(/[^\d,.-]/g, '').replace(',', '.') || '0'
    ) || 0;
    const productId   = document.querySelector('form.cart input[name="product_id"]')?.value || '';

    trackEvent('view_item', {
        currency: 'EUR',
        value: price,
        items: [{ item_id: productId, item_name: productName, price, quantity: 1 }]
    });
});

// EVENTO: select_item
// Clic en un producto desde un listado o categoría.
document.addEventListener('click', function(e) {
    const productLink = e.target.closest('.woocommerce-loop-product__link, .woocommerce-LoopProduct-link');
    if (!productLink) return;

    // No interferir si se abre en nueva pestaña
    if (e.ctrlKey || e.metaKey || e.shiftKey) return;

    e.preventDefault();

    const productEl   = productLink.closest('.product');
    const productName = productEl?.querySelector('.woocommerce-loop-product__title')?.innerText || '';
    const priceEl     = productEl?.querySelector('.woocommerce-Price-amount');
    const price       = parseFloat(
        priceEl?.innerText?.replace(/[^\d,.-]/g, '').replace(',', '.') || '0'
    ) || 0;
    const productId   = productEl?.querySelector('.add_to_cart_button')?.dataset?.productId || '';
    const href        = productLink.href;

    trackEvent('select_item', {
        currency: 'EUR',
        value: price,
        items: [{ item_id: productId, item_name: productName, price, quantity: 1 }]
    });

    // Navegar tras dar tiempo al dataLayer a procesar el evento
    setTimeout(function() {
        window.location.href = href;
    }, 300);
});

// EVENTO: view_cart
// Se dispara al entrar a la página del carrito.
document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector('body.woocommerce-cart')) return;

    const totalEl = document.querySelector('.cart-subtotal .woocommerce-Price-amount');
    const total   = parseFloat(
        totalEl?.innerText?.replace(/[^\d,.-]/g, '').replace(',', '.') || '0'
    ) || 0;

    trackEvent('view_cart', { currency: 'EUR', value: total });
});

// EVENTO: begin_checkout
// Se dispara al entrar a la página de checkout.
document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector('body.woocommerce-checkout')) return;

    trackEvent('begin_checkout', { currency: 'EUR' });
});

// EVENTO: remove_from_cart
// Clic en el enlace "×" para eliminar un producto del carrito.
document.addEventListener('click', function(e) {
    const removeLink = e.target.closest('a.remove');
    if (!removeLink) return;

    // Leer datos del producto antes de que WooCommerce actualice el DOM
    const productId   = removeLink.dataset.product_id || removeLink.getAttribute('data-product_id') || '';
    const row         = removeLink.closest('tr.cart_item, .cart_item');
    const productName = row?.querySelector('.product-name a, .product-name')?.innerText?.trim() || '';
    const priceEl     = row?.querySelector('.product-price .woocommerce-Price-amount, .product-subtotal .woocommerce-Price-amount');
    const price       = parseFloat(
        priceEl?.innerText?.replace(/[^\d,.-]/g, '').replace(',', '.') || '0'
    ) || 0;

    if (!productId) return; // no es un botón de eliminar de WooCommerce

    trackEvent('remove_from_cart', {
        currency: 'EUR',
        value: price,
        items: [{ item_id: productId, item_name: productName, price, quantity: 1 }]
    });
});

// EVENTO: search
// Se dispara al llegar a una página de resultados de búsqueda.
document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector('body.search-results')) return;

    const term = new URLSearchParams(window.location.search).get('s') || '';
    if (!term) return;

    trackEvent('search', { search_term: term });
});
