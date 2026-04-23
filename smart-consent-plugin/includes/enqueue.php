<?php

add_action('wp_head', function() {
    $consented = isset($_COOKIE['smart_consent']) && $_COOKIE['smart_consent'] === 'accepted';

    // Cargar Google Font si se eligió una en ajustes
    $font         = get_option('smart_banner_font_family', '');
    $google_fonts = ['Roboto', 'Open Sans', 'Lato', 'Montserrat', 'Poppins'];
    if (in_array($font, $google_fonts)) {
        $font_url = 'https://fonts.googleapis.com/css2?family=' . urlencode($font) . ':wght@400;500;600&display=swap';
        echo "<link rel='stylesheet' href='" . esc_url($font_url) . "'>";
    }

    // Inyectar variables CSS con los valores guardados en ajustes
    $bg_color        = get_option('smart_banner_bg_color',    '#ffffff');
    $text_color      = get_option('smart_banner_text_color',  '#333333');
    $font_family     = $font ? "'{$font}', sans-serif" : '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif';
    $btn_accept_bg   = get_option('smart_btn_accept_bg',      '#0073aa');
    $btn_accept_text = get_option('smart_btn_accept_text',    '#ffffff');
    $btn_reject_bg   = get_option('smart_btn_reject_bg',      '#ffffff');
    $btn_reject_text = get_option('smart_btn_reject_text',    '#0073aa');
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
        }
    </style>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}

        gtag('consent', 'default', {
            'ad_storage':         'denied',
            'analytics_storage':  'denied',
            'ad_user_data':       'denied',
            'ad_personalization': 'denied',
            'wait_for_update':    500
        });

        <?php if ($consented): ?>
        gtag('consent', 'update', {
            'ad_storage':         'granted',
            'analytics_storage':  'granted',
            'ad_user_data':       'granted',
            'ad_personalization': 'granted'
        });
        <?php endif; ?>
    </script>
    <?php
}, 1);


add_action('wp_enqueue_scripts', function() {

    wp_enqueue_script(
        'smart-consent-core',
        plugin_dir_url(__FILE__) . '../public/js/consent-core.js',
        [],
        '2.0',
        true
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

    $just_logged_in = false;
    if (is_user_logged_in() && isset($_COOKIE['woocommerce_just_logged_in'])) {
        $just_logged_in = true;
        setcookie('woocommerce_just_logged_in', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
    }

    $just_registered = isset($_GET['account-registered']) || isset($_GET['registered']);

    wp_localize_script('smart-consent-core', 'smartSettings', [
        'ajax_url'       => admin_url('admin-ajax.php'),
        'nonce'          => wp_create_nonce('smart_consent_nonce'),
        'debug'          => get_option('smart_debug_mode'),
        'consented'      => isset($_COOKIE['smart_consent']) && $_COOKIE['smart_consent'] === 'accepted' ? 'true' : 'false',
        'justLoggedIn'   => $just_logged_in  ? 'true' : 'false',
        'justRegistered' => $just_registered ? 'true' : 'false',
    ]);
});

add_action('woocommerce_login', function() {
    setcookie('woocommerce_just_logged_in', '1', time() + 60, COOKIEPATH, COOKIE_DOMAIN);
}, 10, 2);