<?php

if (!defined('ABSPATH')) exit;

add_action('wp_ajax_save_consent', 'smart_save_consent');
add_action('wp_ajax_nopriv_save_consent', 'smart_save_consent');

function smart_save_consent() {

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'smart_consent_nonce')) {
        wp_send_json_error(['message' => 'Nonce inválido'], 403);
        return;
    }

    $consent = isset($_POST['consent']) ? sanitize_text_field($_POST['consent']) : 'unknown';

    // Guardar en cookie (30 días)
    setcookie('smart_consent', $consent, time() + (86400 * 30), "/");

    wp_send_json_success([
        'message' => 'Consentimiento guardado'
    ]);
}
