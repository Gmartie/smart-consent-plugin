<?php

if (!defined('ABSPATH')) exit;

// Añadir menú
add_action('admin_menu', function() {
    add_menu_page(
        'Smart Consent',
        'Smart Consent',
        'manage_options',
        'smart-consent',
        'smart_consent_settings_page',
        'dashicons-shield'
    );
});

function smart_consent_settings_page() {
    ?>
    <div class="wrap">
        <h1>Smart Consent Settings</h1>

        <form method="post" action="options.php">
            <?php
            settings_fields('smart_consent_group');
            do_settings_sections('smart-consent');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
add_action('admin_init', function() {

    register_setting('smart_consent_group', 'smart_ga_id');
    register_setting('smart_consent_group', 'smart_enable_analytics');
    register_setting('smart_consent_group', 'smart_enable_ads');
    register_setting('smart_consent_group', 'smart_banner_text');
    register_setting('smart_consent_group', 'smart_debug_mode');

});

add_action('admin_init', function() {

    add_settings_section(
        'smart_main_section',
        'Configuración General',
        null,
        'smart-consent'
    );

    add_settings_field(
        'smart_ga_id',
        'Google ID (GA4 o GTM)',
        function() {
            $value = get_option('smart_ga_id');
            echo "<input type='text' name='smart_ga_id' value='$value' />";
        },
        'smart-consent',
        'smart_main_section'
    );

    add_settings_field(
        'smart_enable_analytics',
        'Activar Analytics',
        function() {
            $value = get_option('smart_enable_analytics');
            echo "<input type='checkbox' name='smart_enable_analytics' value='1' " . checked(1, $value, false) . " />";
        },
        'smart-consent',
        'smart_main_section'
    );

    add_settings_field(
        'smart_enable_ads',
        'Activar Ads',
        function() {
            $value = get_option('smart_enable_ads');
            echo "<input type='checkbox' name='smart_enable_ads' value='1' " . checked(1, $value, false) . " />";
        },
        'smart-consent',
        'smart_main_section'
    );

    add_settings_field(
        'smart_banner_text',
        'Texto del banner',
        function() {
            $value = get_option('smart_banner_text');
            echo "<textarea name='smart_banner_text'>$value</textarea>";
        },
        'smart-consent',
        'smart_main_section'
    );

    add_settings_field(
        'smart_debug_mode',
        'Modo debug',
        function() {
            $value = get_option('smart_debug_mode');
            echo "<input type='checkbox' name='smart_debug_mode' value='1' " . checked(1, $value, false) . " />";
        },
        'smart-consent',
        'smart_main_section'
    );

});

