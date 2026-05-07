<?php
/**
 * - Inyecta Google Consent Mode v2 (estado inicial) en el <head> ANTES que GTM.
 * - Inyecta el snippet de Google Tag Manager (si se ha configurado un GTM ID).
 * - Inyecta el snippet de Google Analytics 4 directamente (si se ha configurado
 *   un Measurement ID y NO se usa GTM), como modo alternativo.
 * - Carga los scripts JS del plugin y pasa las variables de configuración.
 */

if (!defined('ABSPATH')) exit;

//CONSENT MODE  + GTM/GA4 — inyectado en <head> con prioridad máxima
add_action('wp_head', function () {

    $consented  = isset($_COOKIE['smart_consent']) && $_COOKIE['smart_consent'] === 'accepted';
    $gtm_id     = trim(get_option('smart_gtm_id', ''));
    $ga4_id     = trim(get_option('smart_ga4_id', ''));
    $use_gtm    = !empty($gtm_id);
    $use_ga4    = !empty($ga4_id);

    //Google Font (si el usuario eligió una en ajustes)
    $font         = get_option('smart_banner_font_family', '');
    $google_fonts = ['Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Poppins'];
    if (in_array($font, $google_fonts)) {
        $font_url = 'https://fonts.googleapis.com/css2?family=' . urlencode($font) . ':wght@400;500;600&display=swap';
        echo "<link rel='stylesheet' href='" . esc_url($font_url) . "'>\n";
    }

    //Variables CSS del banner
    $bg_color        = get_option('smart_banner_bg_color',    '#ffffff');
    $text_color      = get_option('smart_banner_text_color',  '#333333');
    $font_family     = $font ? "'{$font}', sans-serif" : '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif';
    $btn_accept_bg   = get_option('smart_btn_accept_bg',      '#0073aa');
    $btn_accept_text = get_option('smart_btn_accept_text',    '#ffffff');
    $btn_reject_bg   = get_option('smart_btn_reject_bg',      '#ffffff');
    $btn_reject_text = get_option('smart_btn_reject_text',    '#0073aa');
    $trigger_bg           = get_option('smart_trigger_bg',            '#0073aa');
    $trigger_icon_color   = get_option('smart_trigger_icon_color',    '#ffffff');
    $banner_icon_color    = get_option('smart_banner_icon_color',     '#4a1050');
    ?>
    <style>
        :root {
            --scp-bg:              <?php echo esc_attr($bg_color); ?>;
            --scp-text:            <?php echo esc_attr($text_color); ?>;
            --scp-font:            <?php echo $font_family; ?>;
            --scp-btn-accept-bg:   <?php echo esc_attr($btn_accept_bg); ?>;
            --scp-btn-accept-text: <?php echo esc_attr($btn_accept_text); ?>;
            --scp-btn-reject-bg:   <?php echo esc_attr($btn_reject_bg); ?>;
            --scp-btn-reject-text: <?php echo esc_attr($btn_reject_text); ?>;
            --scp-trigger-bg:          <?php echo esc_attr($trigger_bg); ?>;
            --scp-trigger-color:       <?php echo esc_attr($trigger_icon_color); ?>;
            --scp-trigger-icon-color:  <?php echo esc_attr($trigger_icon_color); ?>;
            --scp-icon-color:          <?php echo esc_attr($banner_icon_color); ?>;
        }
    </style>

    <script>
        //Google Consent Mode v2: estado inicial (siempre "denied") 
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}

        gtag('consent', 'default', {
            'ad_storage':         'denied',
            'analytics_storage':  'denied',
            'ad_user_data':       'denied',
            'ad_personalization': 'denied',
            'wait_for_update':    500
        });

        <?php if ($consented) : ?>
        // Usuario ya había aceptado: actualizar estado antes de cargar GTM/GA4
        gtag('consent', 'update', {
            'ad_storage':         'granted',
            'analytics_storage':  'granted',
            'ad_user_data':       'granted',
            'ad_personalization': 'granted'
        });
        <?php endif; ?>
    </script>

    <?php if ($use_gtm) : ?>
    <!-- Google Tag Manager — inyectado por Smart Consent Tracker -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?php echo esc_js($gtm_id); ?>');</script>
    <!-- End Google Tag Manager -->

    <?php elseif ($use_ga4) : ?>
    <!-- Google Analytics 4 directo — inyectado por Smart Consent Tracker -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr($ga4_id); ?>"></script>
    <script>
        gtag('js', new Date());
        gtag('config', '<?php echo esc_js($ga4_id); ?>', {
            'send_page_view': true
        });
    </script>
    <!-- End Google Analytics 4 -->
    <?php endif; ?>

    <?php
}, 1); // Prioridad 1 = antes que cualquier otro script del tema


//GTM <noscript> — inyectado justo después de <body> (requisito de Google)
add_action('wp_body_open', function () {
    $gtm_id = trim(get_option('smart_gtm_id', ''));
    if (empty($gtm_id)) return;
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr($gtm_id); ?>"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
});


//Scripts y estilos del plugin
add_action('wp_enqueue_scripts', function () {

    $plugin_url = plugin_dir_url(dirname(__FILE__));

    wp_enqueue_script(
        'smart-consent-core',
        $plugin_url . 'public/js/consent-core.js',
        [],
        '3.0',
        true
    );

    wp_enqueue_script(
        'event-queue',
        $plugin_url . 'public/js/event-queue.js',
        ['smart-consent-core'],
        '3.0',
        true
    );

    wp_enqueue_script(
        'smart-integrations',
        $plugin_url . 'public/js/integrations.js',
        ['smart-consent-core', 'event-queue'],
        '3.0',
        true
    );

    wp_enqueue_style(
        'smart-consent-css',
        $plugin_url . 'public/css/banner.css',
        [],
        '3.0'
    );

    // Detectar login y registro para disparar eventos
    $just_logged_in = false;
    if (is_user_logged_in() && isset($_COOKIE['woocommerce_just_logged_in'])) {
        $just_logged_in = true;
        setcookie('woocommerce_just_logged_in', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
    }
    $just_registered = isset($_GET['account-registered']) || isset($_GET['registered']);

    $gtm_id  = trim(get_option('smart_gtm_id', ''));
    $ga4_id  = trim(get_option('smart_ga4_id', ''));
    $use_gtm = !empty($gtm_id);
    $use_ga4 = !$use_gtm && !empty($ga4_id);

    wp_localize_script('smart-consent-core', 'smartSettings', [
        'ajax_url'       => admin_url('admin-ajax.php'),
        'nonce'          => wp_create_nonce('smart_consent_nonce'),
        'debug'          => get_option('smart_debug_mode'),
        'consented'      => isset($_COOKIE['smart_consent']) && $_COOKIE['smart_consent'] === 'accepted' ? 'true' : 'false',
        'justLoggedIn'   => $just_logged_in  ? 'true' : 'false',
        'justRegistered' => $just_registered ? 'true' : 'false',
        'useGTM'         => $use_gtm  ? 'true' : 'false',
        'useGA4'         => $use_ga4  ? 'true' : 'false',
        'ga4Id'          => esc_js($ga4_id),
        'cookieExists'   => isset($_COOKIE['smart_consent']) ? 'true' : 'false',
    ]);
});


//Cookie temporal para detectar login de WooCommerce
add_action('woocommerce_login', function () {
    setcookie('woocommerce_just_logged_in', '1', time() + 60, COOKIEPATH, COOKIE_DOMAIN);
}, 10, 2);
