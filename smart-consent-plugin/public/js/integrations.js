// EVENTO: view_item_list
// Se dispara al cargar una página de categoría, tienda o listado de productos.
// Registra todos los productos visibles en el listado.
document.addEventListener('DOMContentLoaded', function() {
    const isShopPage = document.querySelector('body.woocommerce-shop, body.tax-product_cat, body.tax-product_tag');
    if (!isShopPage) return;

    const productEls = document.querySelectorAll('li.product');
    if (!productEls.length) return;

    const listName = document.querySelector('h1.woocommerce-products-header__title, h1.page-title')?.innerText?.trim() || 'Product List';

    const items = Array.from(productEls).map(function(el, index) {
        const name    = el.querySelector('.woocommerce-loop-product__title')?.innerText?.trim() || '';
        const priceEl = el.querySelector('.woocommerce-Price-amount');
        const price   = parseFloat(
            priceEl?.innerText?.replace(/[^\d,.-]/g, '').replace(',', '.') || '0'
        ) || 0;
        const id      = el.querySelector('.add_to_cart_button')?.dataset?.productId || '';

        return { item_id: id, item_name: name, price, index: index + 1, item_list_name: listName };
    });

    trackEvent('view_item_list', {
        item_list_name: listName,
        items
    });

    if (smartSettings.debug) {
        console.log('[SmartConsent] view_item_list:', listName, items);
    }
});

// EVENTO: add_to_cart
// 1. Producto simple en listado
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

// 2. Producto variable en página de detalle
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

    const categoryEl  = document.querySelector('.posted_in a, nav.woocommerce-breadcrumb a:nth-last-child(2)');
    const category    = categoryEl?.innerText?.trim() || '';

    trackEvent('view_item', {
        currency: 'EUR',
        value: price,
        items: [{
            item_id:       productId,
            item_name:     productName,
            price,
            quantity:      1,
            item_category: category || undefined
        }]
    });

    if (smartSettings.debug) {
        console.log('[SmartConsent] view_item:', { productId, productName, price, category });
    }
});

// EVENTO: select_item
// Clic en un producto desde un listado o categoría.
document.addEventListener('click', function(e) {
    const productLink = e.target.closest('.woocommerce-loop-product__link, .woocommerce-LoopProduct-link');
    if (!productLink) return;

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

    const listName = document.querySelector('h1.woocommerce-products-header__title, h1.page-title')?.innerText?.trim() || 'Product List';

    trackEvent('select_item', {
        currency: 'EUR',
        value: price,
        item_list_name: listName,
        items: [{ item_id: productId, item_name: productName, price, quantity: 1 }]
    });

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

    const items = [];
    document.querySelectorAll('tr.cart_item, .cart_item').forEach(function(row) {
        const name    = row.querySelector('.product-name a, .product-name')?.innerText?.trim() || '';
        const priceEl = row.querySelector('.product-price .woocommerce-Price-amount, .product-subtotal .woocommerce-Price-amount');
        const price   = parseFloat(
            priceEl?.innerText?.replace(/[^\d,.-]/g, '').replace(',', '.') || '0'
        ) || 0;
        const qty     = parseInt(row.querySelector('input.qty, .product-quantity .qty')?.value || '1', 10) || 1;
        const id      = row.querySelector('a.remove')?.getAttribute('data-product_id') || '';
        if (name) items.push({ item_id: id, item_name: name, price, quantity: qty });
    });

    trackEvent('view_cart', { currency: 'EUR', value: total, items });

    if (smartSettings.debug) {
        console.log('[SmartConsent] view_cart:', { total, items });
    }
});

// EVENTO: begin_checkout
// Se dispara al entrar a la página de checkout.
document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector('body.woocommerce-checkout')) return;

    const totalEl = document.querySelector('.order-total .woocommerce-Price-amount');
    const total   = parseFloat(
        totalEl?.innerText?.replace(/[^\d,.-]/g, '').replace(',', '.') || '0'
    ) || 0;

    trackEvent('begin_checkout', { currency: 'EUR', value: total || undefined });

    if (smartSettings.debug) {
        console.log('[SmartConsent] begin_checkout:', { total });
    }
});

// EVENTO: add_shipping_info
// Se dispara al cargar el checkout si ya hay un método preseleccionado,
// y también al cambiar la selección manualmente.
document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector('body.woocommerce-checkout')) return;

    function fireShippingEvent(input) {
        const shippingMethod = input.value || '';
        const shippingLabel  = input.closest('li')?.querySelector('label')?.innerText?.trim() || shippingMethod;
        const totalEl        = document.querySelector('.order-total .woocommerce-Price-amount');
        const total          = parseFloat(
            totalEl?.innerText?.replace(/[^\d,.-]/g, '').replace(',', '.') || '0'
        ) || 0;

        trackEvent('add_shipping_info', {
            currency:      'EUR',
            value:         total || undefined,
            shipping_tier: shippingLabel
        });

        if (smartSettings.debug) {
            console.log('[SmartConsent] add_shipping_info:', { shippingMethod, shippingLabel });
        }
    }

    // Disparar con la opción preseleccionada al cargar la página
    const preselectedShipping = document.querySelector('input[name^="shipping_method"]:checked');
    if (preselectedShipping) {
        fireShippingEvent(preselectedShipping);
    }

    // Disparar también si el usuario cambia la selección
    document.addEventListener('change', function(e) {
        if (!e.target.matches('input[name^="shipping_method"]')) return;
        fireShippingEvent(e.target);
    });
});

// EVENTO: add_payment_info
// Se dispara al cargar el checkout si ya hay un método preseleccionado,
// y también al cambiar la selección manualmente.
document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector('body.woocommerce-checkout')) return;

    function firePaymentEvent(input) {
        const paymentMethod = input.value || '';
        const totalEl       = document.querySelector('.order-total .woocommerce-Price-amount');
        const total         = parseFloat(
            totalEl?.innerText?.replace(/[^\d,.-]/g, '').replace(',', '.') || '0'
        ) || 0;

        trackEvent('add_payment_info', {
            currency:     'EUR',
            value:        total || undefined,
            payment_type: paymentMethod
        });

        if (smartSettings.debug) {
            console.log('[SmartConsent] add_payment_info:', { paymentMethod });
        }
    }

    // Disparar con la opción preseleccionada al cargar la página
    const preselectedPayment = document.querySelector('input[name="payment_method"]:checked');
    if (preselectedPayment) {
        firePaymentEvent(preselectedPayment);
    }

    // Disparar también si el usuario cambia la selección
    document.addEventListener('change', function(e) {
        if (!e.target.matches('input[name="payment_method"]')) return;
        firePaymentEvent(e.target);
    });
});

// EVENTO: purchase
// Se dispara en la página de confirmación de pedido (order-received).
// Usa sessionStorage para evitar duplicados si el usuario recarga la página.
document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector('body.woocommerce-order-received')) return;

    const urlParams = new URLSearchParams(window.location.search);
    const orderId   = urlParams.get('order') ||
                      document.querySelector('.woocommerce-order-overview__order strong')?.innerText?.trim() || '';

    if (!orderId) return;

    const flagKey = 'scp_purchase_fired_' + orderId;
    if (sessionStorage.getItem(flagKey)) {
        if (smartSettings.debug) console.log('[SmartConsent] purchase ya enviado para pedido:', orderId);
        return;
    }

    const totalEl = document.querySelector('.woocommerce-order-overview__total .woocommerce-Price-amount');
    const total   = parseFloat(
        totalEl?.innerText?.replace(/[^\d,.-]/g, '').replace(',', '.') || '0'
    ) || 0;

    const taxEl = document.querySelector('.woocommerce-order-overview__tax .woocommerce-Price-amount');
    const tax   = parseFloat(
        taxEl?.innerText?.replace(/[^\d,.-]/g, '').replace(',', '.') || '0'
    ) || 0;

    const shippingEl = document.querySelector('.woocommerce-order-overview__shipping .woocommerce-Price-amount');
    const shipping   = parseFloat(
        shippingEl?.innerText?.replace(/[^\d,.-]/g, '').replace(',', '.') || '0'
    ) || 0;

    const items = [];
    document.querySelectorAll('.woocommerce-table--order-details tbody tr, .woocommerce-order-item').forEach(function(row) {
        const nameEl    = row.querySelector('.woocommerce-table__product-name a, .woocommerce-table__product-name');
        const name      = nameEl?.innerText?.trim() || '';
        if (!name) return;

        const qtyEl     = row.querySelector('.woocommerce-table__product-total .product-quantity, .product-quantity');
        const qty       = parseInt(qtyEl?.innerText?.replace(/[^\d]/g, '') || '1', 10) || 1;
        const priceEl   = row.querySelector('.woocommerce-table__product-total .woocommerce-Price-amount');
        const price     = parseFloat(
            priceEl?.innerText?.replace(/[^\d,.-]/g, '').replace(',', '.') || '0'
        ) || 0;
        const unitPrice = qty > 0 ? price / qty : price;

        items.push({ item_name: name, price: unitPrice, quantity: qty });
    });

    trackEvent('purchase', {
        currency:       'EUR',
        transaction_id: orderId,
        value:          total,
        tax:            tax || undefined,
        shipping:       shipping || undefined,
        items:          items.length ? items : undefined
    });

    sessionStorage.setItem(flagKey, '1');

    if (smartSettings.debug) {
        console.log('[SmartConsent] purchase:', { orderId, total, tax, shipping, items });
    }
});

// EVENTO: remove_from_cart
// Clic en el enlace "×" para eliminar un producto del carrito.
document.addEventListener('click', function(e) {
    const removeLink = e.target.closest('a.remove');
    if (!removeLink) return;

    const productId   = removeLink.dataset.product_id || removeLink.getAttribute('data-product_id') || '';
    const row         = removeLink.closest('tr.cart_item, .cart_item');
    const productName = row?.querySelector('.product-name a, .product-name')?.innerText?.trim() || '';
    const priceEl     = row?.querySelector('.product-price .woocommerce-Price-amount, .product-subtotal .woocommerce-Price-amount');
    const price       = parseFloat(
        priceEl?.innerText?.replace(/[^\d,.-]/g, '').replace(',', '.') || '0'
    ) || 0;

    if (!productId) return;

    trackEvent('remove_from_cart', {
        currency: 'EUR',
        value: price,
        items: [{ item_id: productId, item_name: productName, price, quantity: 1 }]
    });

    if (smartSettings.debug) {
        console.log('[SmartConsent] remove_from_cart:', { productId, productName, price });
    }
});

// EVENTO: search
// Se dispara al llegar a una página de resultados de búsqueda.
document.addEventListener('DOMContentLoaded', function() {
    if (!document.querySelector('body.search-results')) return;

    const term = new URLSearchParams(window.location.search).get('s') || '';
    if (!term) return;

    trackEvent('search', { search_term: term });

    if (smartSettings.debug) {
        console.log('[SmartConsent] search:', { term });
    }
});

// EVENTO: login
// Detectado desde PHP via cookie temporal establecida en el hook
// 'woocommerce_login'. Más fiable que leer parámetros de URL.
document.addEventListener('DOMContentLoaded', function() {
    if (smartSettings.justLoggedIn !== 'true') return;

    trackEvent('login', { method: 'WooCommerce' });

    if (smartSettings.debug) {
        console.log('[SmartConsent] login detectado via PHP.');
    }
});

// EVENTO: sign_up
// Detectado desde PHP comprobando el parámetro ?account-registered en la URL.
document.addEventListener('DOMContentLoaded', function() {
    if (smartSettings.justRegistered !== 'true') return;

    trackEvent('sign_up', { method: 'WooCommerce' });

    if (smartSettings.debug) {
        console.log('[SmartConsent] sign_up detectado via PHP.');
    }
});