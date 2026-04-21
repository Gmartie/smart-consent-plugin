<?php
/**
 * CORRECCIÓN CLAVE #1:
 * El "consent default" debe enviarse al dataLayer ANTES de que GTM cargue.
 * Por eso usamos wp_head con prioridad 1 (muy alta) para que se ejecute
 * antes que el snippet de GTM que GTM4WP inyecta en prioridad normal.
 *
 * Si este bloque llegara después de GTM, Google lo ignoraría por completo.
 */
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


/**
 * CORRECCIÓN CLAVE #2:
 * Los scripts del banner y la lógica de consentimiento van al FOOTER,
 * lo cual está bien para la UI. Pero ya NO cargan GA4 directamente.
 * GA4 ahora vive dentro de GTM como etiqueta, y GTM decide cuándo
 * dispararlo según el estado del consentimiento.
 */
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

    // Pasamos ajustes al JS. Ya no se pasa ga_id porque GA4
    // lo gestiona GTM internamente, no este plugin.
    wp_localize_script('smart-consent-core', 'smartSettings', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('smart_consent_nonce'),
        'debug'    => get_option('smart_debug_mode'),
        'consented'=> isset($_COOKIE['smart_consent']) && $_COOKIE['smart_consent'] === 'accepted' ? 'true' : 'false',
    ]);
});
