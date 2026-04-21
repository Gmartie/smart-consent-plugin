<?php

if (!defined('ABSPATH')) exit;

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
        <div class="notice notice-info">
            <p><strong>Nota:</strong> Este plugin gestiona el consentimiento y envía señales a GTM vía Consent Mode v2.
            GA4 debe estar configurado como etiqueta <em>dentro de Google Tag Manager</em>, no aquí.
            Este plugin solo necesita estar activo junto con el plugin GTM4WP.</p>
        </div>
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
        'smart_enable_analytics',
        'Activar Analytics (Consent Mode)',
        function() {
            $value = get_option('smart_enable_analytics');
            echo "<input type='checkbox' name='smart_enable_analytics' value='1' " . checked(1, $value, false) . " />";
            echo "<p class='description'>Habilita las señales de analytics_storage en Consent Mode v2.</p>";
        },
        'smart-consent',
        'smart_main_section'
    );

    add_settings_field(
        'smart_enable_ads',
        'Activar Ads (Consent Mode)',
        function() {
            $value = get_option('smart_enable_ads');
            echo "<input type='checkbox' name='smart_enable_ads' value='1' " . checked(1, $value, false) . " />";
            echo "<p class='description'>Habilita las señales de ad_storage en Consent Mode v2.</p>";
        },
        'smart-consent',
        'smart_main_section'
    );

    add_settings_field(
        'smart_banner_text',
        'Texto del banner',
        function() {
            $value = get_option('smart_banner_text');
            echo "<textarea name='smart_banner_text' rows='3' cols='50'>$value</textarea>";
        },
        'smart-consent',
        'smart_main_section'
    );

    add_settings_field(
        'smart_debug_mode',
        'Modo debug (consola)',
        function() {
            $value = get_option('smart_debug_mode');
            echo "<input type='checkbox' name='smart_debug_mode' value='1' " . checked(1, $value, false) . " />";
            echo "<p class='description'>Muestra mensajes en la consola del navegador para depuración.</p>";
        },
        'smart-consent',
        'smart_main_section'
    );
});
