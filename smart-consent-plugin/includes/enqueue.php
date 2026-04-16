<?php
add_action('wp_enqueue_scripts', function() {

    wp_enqueue_script(
        'smart-consent-core',
        plugin_dir_url(__FILE__) . '../public/js/consent-core.js',
        [],
        '1.0',
        true
    );

    wp_enqueue_script(
        'event-queue',
        plugin_dir_url(__FILE__) . '../public/js/event-queue.js',
        ['smart-consent-core'], // depende de consent-core (donde está gtag)
        '1.0',
        true
    );

    wp_enqueue_script(
        'smart-integrations',
        plugin_dir_url(__FILE__) . '../public/js/integrations.js',
        ['smart-consent-core', 'event-queue'], // depende de trackEvent definido en event-queue
        '1.0',
        true
    );

    wp_localize_script('smart-consent-core', 'smartSettings', [
        'ajax_url'  => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('smart_consent_nonce'),
        'ga_id'     => get_option('smart_ga_id'),
        'analytics' => get_option('smart_enable_analytics'),
        'ads'       => get_option('smart_enable_ads'),
        'debug'     => get_option('smart_debug_mode'),
    ]);
});