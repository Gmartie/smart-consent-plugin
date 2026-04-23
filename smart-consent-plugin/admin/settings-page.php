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

    // Opciones de personalización del banner
    register_setting('smart_consent_group', 'smart_banner_bg_color');
    register_setting('smart_consent_group', 'smart_banner_text_color');
    register_setting('smart_consent_group', 'smart_banner_font_family');
    register_setting('smart_consent_group', 'smart_btn_accept_bg');
    register_setting('smart_consent_group', 'smart_btn_accept_text');
    register_setting('smart_consent_group', 'smart_btn_reject_bg');
    register_setting('smart_consent_group', 'smart_btn_reject_text');
});

add_action('admin_init', function() {

    add_settings_section('smart_main_section', 'Configuración General', null, 'smart-consent');

    add_settings_field(
        'smart_enable_analytics', 'Activar Analytics (Consent Mode)',
        function() {
            $value = get_option('smart_enable_analytics');
            echo "<input type='checkbox' name='smart_enable_analytics' value='1' " . checked(1, $value, false) . " />";
            echo "<p class='description'>Habilita las señales de analytics_storage en Consent Mode v2.</p>";
        },
        'smart-consent', 'smart_main_section'
    );

    add_settings_field(
        'smart_enable_ads', 'Activar Ads (Consent Mode)',
        function() {
            $value = get_option('smart_enable_ads');
            echo "<input type='checkbox' name='smart_enable_ads' value='1' " . checked(1, $value, false) . " />";
            echo "<p class='description'>Habilita las señales de ad_storage en Consent Mode v2.</p>";
        },
        'smart-consent', 'smart_main_section'
    );

    add_settings_field(
        'smart_banner_text', 'Texto del banner',
        function() {
            $value = get_option('smart_banner_text');
            echo "<textarea name='smart_banner_text' rows='3' cols='50'>$value</textarea>";
        },
        'smart-consent', 'smart_main_section'
    );

    add_settings_field(
        'smart_debug_mode', 'Modo debug (consola)',
        function() {
            $value = get_option('smart_debug_mode');
            echo "<input type='checkbox' name='smart_debug_mode' value='1' " . checked(1, $value, false) . " />";
            echo "<p class='description'>Muestra mensajes en la consola del navegador para depuración.</p>";
        },
        'smart-consent', 'smart_main_section'
    );

    // -------------------------------------------------------------------------
    // SECCIÓN: Personalización del Banner
    // Usa wp-color-picker (incluido en WordPress core) para cada campo de color.
    // Los campos se renderizan como inputs de texto con clase 'scp-color-picker'
    // que wp-color-picker convierte en un selector visual completo.
    // -------------------------------------------------------------------------
    add_settings_section(
        'smart_banner_design_section',
        'Personalización del Banner',
        function() {
            echo "<p>Personaliza los colores y tipografía del banner de cookies.</p>";
        },
        'smart-consent'
    );

    // Helper para renderizar cada campo de color con wp-color-picker
    $color_field = function($option, $default) {
        $value = get_option($option, $default);
        echo "<input type='text'
                     name='" . esc_attr($option) . "'
                     value='" . esc_attr($value) . "'
                     class='scp-color-picker'
                     data-default-color='" . esc_attr($default) . "' />";
    };

    add_settings_field('smart_banner_bg_color', 'Fondo del banner',
        function() use ($color_field) { $color_field('smart_banner_bg_color', '#ffffff'); },
        'smart-consent', 'smart_banner_design_section'
    );

    add_settings_field('smart_banner_text_color', 'Color del texto',
        function() use ($color_field) { $color_field('smart_banner_text_color', '#333333'); },
        'smart-consent', 'smart_banner_design_section'
    );

    // Tipografía: selector con fuentes de sistema y Google Fonts
    add_settings_field(
        'smart_banner_font_family', 'Tipografía',
        function() {
            $value   = get_option('smart_banner_font_family', '');
            $options = [
                ''             => 'Por defecto (sistema)',
                'Arial'        => 'Arial',
                'Georgia'      => 'Georgia',
                'Verdana'      => 'Verdana',
                'Trebuchet MS' => 'Trebuchet MS',
                'Roboto'       => 'Roboto (Google Fonts)',
                'Open Sans'    => 'Open Sans (Google Fonts)',
                'Lato'         => 'Lato (Google Fonts)',
                'Montserrat'   => 'Montserrat (Google Fonts)',
                'Poppins'      => 'Poppins (Google Fonts)',
            ];
            echo "<select name='smart_banner_font_family'>";
            foreach ($options as $font => $label) {
                echo "<option value='" . esc_attr($font) . "' " . selected($value, $font, false) . ">$label</option>";
            }
            echo "</select>";
            echo "<p class='description'>Las fuentes de Google Fonts se cargan automáticamente.</p>";
        },
        'smart-consent', 'smart_banner_design_section'
    );

    add_settings_field('smart_btn_accept_bg', 'Botón Aceptar — fondo',
        function() use ($color_field) { $color_field('smart_btn_accept_bg', '#0073aa'); },
        'smart-consent', 'smart_banner_design_section'
    );

    add_settings_field('smart_btn_accept_text', 'Botón Aceptar — texto',
        function() use ($color_field) { $color_field('smart_btn_accept_text', '#ffffff'); },
        'smart-consent', 'smart_banner_design_section'
    );

    add_settings_field('smart_btn_reject_bg', 'Botón Rechazar — fondo',
        function() use ($color_field) { $color_field('smart_btn_reject_bg', '#ffffff'); },
        'smart-consent', 'smart_banner_design_section'
    );

    add_settings_field('smart_btn_reject_text', 'Botón Rechazar — texto',
        function() use ($color_field) { $color_field('smart_btn_reject_text', '#0073aa'); },
        'smart-consent', 'smart_banner_design_section'
    );
});

// Cargar wp-color-picker (JS + CSS) solo en la página de ajustes del plugin
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook !== 'toplevel_page_smart-consent') return;

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('scp-color-picker-init',
        plugin_dir_url(__FILE__) . '../public/js/admin-color-picker.js',
        ['wp-color-picker'],
        '2.0',
        true
    );
});