<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_footer', 'scp_render_banner' );

function scp_render_banner() {
    include SCP_PLUGIN_DIR . 'templates/banner.php';
}

function scp_get_consent_status() {
    if ( isset( $_COOKIE['scp_consent'] ) ) {
        return sanitize_text_field( $_COOKIE['scp_consent'] );
    }
    return null;
}

function scp_has_consent( $type = 'all' ) {
    $status = scp_get_consent_status();
    if ( null === $status ) {
        return false;
    }
    $consent = json_decode( $status, true );
    if ( 'all' === $type ) {
        return ! empty( $consent['accepted'] );
    }
    return isset( $consent['categories'][ $type ] ) && true === $consent['categories'][ $type ];
}
