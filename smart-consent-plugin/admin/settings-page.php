<?php
/**
 * settings-page.php
 *
 * Página de ajustes del plugin en el panel de WordPress.
 * Incluye configuración de GTM y GA4 directamente, sin depender de plugins externos.
 */

if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
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
    $gtm_id = trim(get_option('smart_gtm_id', ''));
    $ga4_id = trim(get_option('smart_ga4_id', ''));
    ?>
    <div class="wrap">
        <h1>Smart Consent Settings</h1>

        <?php if (empty($gtm_id) && empty($ga4_id)) : ?>
        <div class="notice notice-warning">
            <p><strong>Atención:</strong> No has configurado ningún destino de analítica.
            Introduce un <strong>GTM Container ID</strong> (recomendado) o un <strong>GA4 Measurement ID</strong>
            en la sección "Integración con Google" para que los eventos se envíen correctamente.</p>
        </div>
        <?php elseif (!empty($gtm_id) && !empty($ga4_id)) : ?>
        <div class="notice notice-warning">
            <p><strong>Atención:</strong> Tienes configurados tanto GTM como GA4. Cuando ambos están
            presentes, <strong>GTM tiene prioridad</strong> y GA4 directo se ignora. Configura GA4 como
            etiqueta dentro de GTM y deja vacío el campo GA4 Measurement ID si no lo necesitas por separado.</p>
        </div>
        <?php else : ?>
        <div class="notice notice-success is-dismissible">
            <p>
            <?php if (!empty($gtm_id)) : ?>
                <strong>GTM activo:</strong> Los eventos se envían vía Google Tag Manager (<code><?php echo esc_html($gtm_id); ?></code>).
                Asegúrate de tener la etiqueta de GA4 configurada dentro de GTM.
            <?php else : ?>
                <strong>GA4 directo activo:</strong> Los eventos se envían directamente a Google Analytics 4 (<code><?php echo esc_html($ga4_id); ?></code>).
            <?php endif; ?>
            </p>
        </div>
        <?php endif; ?>

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

add_action('admin_init', function () {

    // Registrar todas las opciones
    $options = [
        'smart_gtm_id',
        'smart_ga4_id',
        'smart_enable_analytics',
        'smart_enable_ads',
        'smart_banner_text',
        'smart_debug_mode',
        'smart_banner_bg_color',
        'smart_banner_text_color',
        'smart_banner_font_family',
        'smart_btn_accept_bg',
        'smart_btn_accept_text',
        'smart_btn_reject_bg',
        'smart_btn_reject_text',
    ];
    foreach ($options as $opt) {
        register_setting('smart_consent_group', $opt);
    }

    //Integración con Google
    add_settings_section(
        'smart_tracking_section',
        'Integración con Google',
        function () {
            echo "<p>Introduce <strong>GTM Container ID</strong> (ej. <code>GTM-XXXXXXX</code>) para usar Google Tag Manager
            — recomendado si ya tienes GTM configurado en tu cuenta —, o bien el <strong>GA4 Measurement ID</strong>
            (ej. <code>G-XXXXXXXXXX</code>) para enviar eventos directamente a Google Analytics 4 sin GTM.<br>
            <em>Si introduces ambos, GTM tiene prioridad y GA4 directo se ignora.</em></p>";
        },
        'smart-consent'
    );

    add_settings_field(
        'smart_gtm_id',
        'GTM Container ID',
        function () {
            $value = get_option('smart_gtm_id', '');
            echo "<input type='text' name='smart_gtm_id' value='" . esc_attr($value) . "'
                  placeholder='GTM-XXXXXXX' class='regular-text' />";
            echo "<p class='description'>El snippet de GTM se inyectará automáticamente. No necesitas instalar ningún otro plugin.</p>";
        },
        'smart-consent',
        'smart_tracking_section'
    );

    add_settings_field(
        'smart_ga4_id',
        'GA4 Measurement ID',
        function () {
            $value = get_option('smart_ga4_id', '');
            echo "<input type='text' name='smart_ga4_id' value='" . esc_attr($value) . "'
                  placeholder='G-XXXXXXXXXX' class='regular-text' />";
            echo "<p class='description'>Solo se usa si el campo GTM Container ID está vacío.
                  En este modo los eventos se envían directamente a GA4.</p>";
        },
        'smart-consent',
        'smart_tracking_section'
    );

    //Configuración General
    add_settings_section('smart_main_section', 'Configuración General', null, 'smart-consent');

    add_settings_field(
        'smart_enable_analytics', 'Activar Analytics (Consent Mode)',
        function () {
            $value = get_option('smart_enable_analytics');
            echo "<input type='checkbox' name='smart_enable_analytics' value='1' " . checked(1, $value, false) . " />";
            echo "<p class='description'>Habilita las señales de <code>analytics_storage</code> en Consent Mode v2.</p>";
        },
        'smart-consent', 'smart_main_section'
    );

    add_settings_field(
        'smart_enable_ads', 'Activar Ads (Consent Mode)',
        function () {
            $value = get_option('smart_enable_ads');
            echo "<input type='checkbox' name='smart_enable_ads' value='1' " . checked(1, $value, false) . " />";
            echo "<p class='description'>Habilita las señales de <code>ad_storage</code> en Consent Mode v2.</p>";
        },
        'smart-consent', 'smart_main_section'
    );

    add_settings_field(
        'smart_banner_text', 'Texto del banner',
        function () {
            $value = get_option('smart_banner_text', '');
            echo "<textarea name='smart_banner_text' rows='3' cols='50'>" . esc_textarea($value) . "</textarea>";
        },
        'smart-consent', 'smart_main_section'
    );

    add_settings_field(
        'smart_debug_mode', 'Modo debug (consola)',
        function () {
            $value = get_option('smart_debug_mode');
            echo "<input type='checkbox' name='smart_debug_mode' value='1' " . checked(1, $value, false) . " />";
            echo "<p class='description'>Muestra mensajes en la consola del navegador para depuración.</p>";
        },
        'smart-consent', 'smart_main_section'
    );

    // SECCIÓN 3 — Personalización del Banner
    add_settings_section(
        'smart_banner_design_section',
        'Personalización del Banner',
        function () {
            echo "<p>Personaliza los colores y tipografía del banner de cookies.</p>";
        },
        'smart-consent'
    );

    // Helper para renderizar campos de color con wp-color-picker
    $color_field = function ($option, $default) {
        $value = get_option($option, $default);
        echo "<input type='text'
                     name='" . esc_attr($option) . "'
                     value='" . esc_attr($value) . "'
                     class='scp-color-picker'
                     data-default-color='" . esc_attr($default) . "' />";
    };

    add_settings_field('smart_banner_bg_color', 'Fondo del banner',
        function () use ($color_field) { $color_field('smart_banner_bg_color', '#ffffff'); },
        'smart-consent', 'smart_banner_design_section'
    );

    add_settings_field('smart_banner_text_color', 'Color del texto',
        function () use ($color_field) { $color_field('smart_banner_text_color', '#333333'); },
        'smart-consent', 'smart_banner_design_section'
    );

    add_settings_field(
        'smart_banner_font_family', 'Tipografía',
        function () {
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
        function () use ($color_field) { $color_field('smart_btn_accept_bg', '#0073aa'); },
        'smart-consent', 'smart_banner_design_section'
    );

    add_settings_field('smart_btn_accept_text', 'Botón Aceptar — texto',
        function () use ($color_field) { $color_field('smart_btn_accept_text', '#ffffff'); },
        'smart-consent', 'smart_banner_design_section'
    );

    add_settings_field('smart_btn_reject_bg', 'Botón Rechazar — fondo',
        function () use ($color_field) { $color_field('smart_btn_reject_bg', '#ffffff'); },
        'smart-consent', 'smart_banner_design_section'
    );

    add_settings_field('smart_btn_reject_text', 'Botón Rechazar — texto',
        function () use ($color_field) { $color_field('smart_btn_reject_text', '#0073aa'); },
        'smart-consent', 'smart_banner_design_section'
    );
});

// Cargar wp-color-picker solo en la página de ajustes del plugin
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_smart-consent') return;

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script(
        'scp-color-picker-init',
        plugin_dir_url(dirname(__FILE__)) . 'public/js/admin-color-picker.js',
        ['wp-color-picker'],
        '3.0',
        true
    );
});
