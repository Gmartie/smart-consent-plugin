<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_ajax_scp_save_consent',        'scp_ajax_save_consent' );
add_action( 'wp_ajax_nopriv_scp_save_consent', 'scp_ajax_save_consent' );

function scp_ajax_save_consent() {
    check_ajax_referer( 'scp_nonce', 'nonce' );

    $consent_value = isset( $_POST['consent'] ) ? sanitize_text_field( wp_unslash( $_POST['consent'] ) ) : '';

    if ( empty( $consent_value ) ) {
        wp_send_json_error( array( 'message' => 'Invalid consent data.' ) );
    }

    $expiry = (int) get_option( 'scp_expiry_days', 180 );

    setcookie(
        'scp_consent',
        $consent_value,
        time() + ( DAY_IN_SECONDS * $expiry ),
        COOKIEPATH,
        COOKIE_DOMAIN,
        is_ssl(),
        true
    );

    wp_send_json_success( array( 'message' => 'Consent saved.' ) );
}

add_action( 'wp_ajax_scp_revoke_consent',        'scp_ajax_revoke_consent' );
add_action( 'wp_ajax_nopriv_scp_revoke_consent', 'scp_ajax_revoke_consent' );

function scp_ajax_revoke_consent() {
    check_ajax_referer( 'scp_nonce', 'nonce' );
    setcookie( 'scp_consent', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
    wp_send_json_success( array( 'message' => 'Consent revoked.' ) );
}
