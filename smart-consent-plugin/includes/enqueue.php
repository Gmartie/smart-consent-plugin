<?php
add_action('wp_head', function() {
    $consented = isset($_COOKIE['smart_consent']) && $_COOKIE['smart_consent'] === 'accepted';
    ?>
    <script>
        // Inicializar dataLayer ANTES de GTM (requerido por Google)
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}

        // Consent Mode v2: estado por defecto bloqueado
        // GTM leerá esto al arrancar y sabrá que debe esperar
        gtag('consent', 'default', {
            'ad_storage':         'denied',
            'analytics_storage':  'denied',
            'ad_user_data':       'denied',
            'ad_personalization': 'denied',
            'wait_for_update':    500
        });

        <?php if ($consented): ?>
        // El usuario ya había aceptado en una visita anterior (cookie presente)
        // Actualizamos el consentimiento inmediatamente, también antes de GTM
        gtag('consent', 'update', {
            'ad_storage':         'granted',
            'analytics_storage':  'granted',
            'ad_user_data':       'granted',
            'ad_personalization': 'granted'
        });
        <?php endif; ?>
    </script>
    <?php
}, 1); // Prioridad 1 = se ejecuta antes que casi todo lo demás en <head>



add_action('wp_enqueue_scripts', function() {

    wp_enqueue_script(
        'smart-consent-core',
        plugin_dir_url(__FILE__) . '../public/js/consent-core.js',
        [],
        '2.0',
        true // footer: ok, la UI del banner no necesita estar en <head>
    );

    wp_enqueue_script(
        'event-queue',
        plugin_dir_url(__FILE__) . '../public/js/event-queue.js',
        ['smart-consent-core'],
        '2.0',
        true
    );

    wp_enqueue_script(
        'smart-integrations',
        plugin_dir_url(__FILE__) . '../public/js/integrations.js',
        ['smart-consent-core', 'event-queue'],
        '2.0',
        true
    );

    wp_enqueue_style(
        'smart-consent-css',
        plugin_dir_url(__FILE__) . '../public/css/banner.css',
        [],
        '2.0'
    );

    // Detectar login reciente: WooCommerce establece una cookie temporal
    // 'woocommerce_just_logged_in' justo después del login.
    $just_logged_in = false;
    if ( is_user_logged_in() && isset($_COOKIE['woocommerce_just_logged_in']) ) {
        $just_logged_in = true;
        // Eliminamos la cookie para que no se dispare en la siguiente página
        setcookie('woocommerce_just_logged_in', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
    }

    // Detectar registro reciente: WooCommerce añade el parámetro
    // 'account-registered' en la URL al redirigir tras el registro.
    $just_registered = isset($_GET['account-registered']) || isset($_GET['registered']);

    wp_localize_script('smart-consent-core', 'smartSettings', [
        'ajax_url'        => admin_url('admin-ajax.php'),
        'nonce'           => wp_create_nonce('smart_consent_nonce'),
        'debug'           => get_option('smart_debug_mode'),
        'consented'       => isset($_COOKIE['smart_consent']) && $_COOKIE['smart_consent'] === 'accepted' ? 'true' : 'false',
        'justLoggedIn'    => $just_logged_in    ? 'true' : 'false',
        'justRegistered'  => $just_registered   ? 'true' : 'false',
    ]);
});

/**
 * Establecer cookie de login cuando WooCommerce procesa el inicio de sesión.
 * Se engancha a 'woocommerce_login' que se dispara justo después de autenticar
 * al usuario, antes de la redirección*/
add_action('woocommerce_login', function() {
    setcookie('woocommerce_just_logged_in', '1', time() + 60, COOKIEPATH, COOKIE_DOMAIN);
}, 10, 2);