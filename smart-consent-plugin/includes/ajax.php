<?php

if (!defined('ABSPATH')) exit;

// Guardar consentimiento
add_action('wp_ajax_save_consent', 'smart_save_consent');
add_action('wp_ajax_nopriv_save_consent', 'smart_save_consent');

function smart_save_consent() {

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'smart_consent_nonce')) {
        wp_send_json_error(['message' => 'Nonce inválido'], 403);
        return;
    }

    $consent = isset($_POST['consent']) ? sanitize_text_field($_POST['consent']) : 'unknown';

    // Guardar en cookie
    setcookie('smart_consent', $consent, time() + (86400 * 30), "/");

    // (guardar en base de datos):
    // global $wpdb;
    // $wpdb->insert('wp_consent_logs', [
    //     'consent' => $consent,
    //     'created_at' => current_time('mysql')
    // ]);

    wp_send_json_success([
        'message' => 'Consentimiento guardado'
    ]);
}