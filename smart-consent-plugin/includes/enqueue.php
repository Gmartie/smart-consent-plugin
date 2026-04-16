<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'wp_enqueue_scripts', 'scp_enqueue_assets' );

function scp_enqueue_assets() {
    wp_enqueue_style(
        'scp-banner',
        SCP_PLUGIN_URL . 'public/css/banner.css',
        array(),
        SCP_VERSION
    );

    wp_enqueue_script(
        'scp-event-queue',
        SCP_PLUGIN_URL . 'public/js/event-queue.js',
        array(),
        SCP_VERSION,
        true
    );

    wp_enqueue_script(
        'scp-integrations',
        SCP_PLUGIN_URL . 'public/js/integrations.js',
        array( 'scp-event-queue' ),
        SCP_VERSION,
        true
    );

    wp_enqueue_script(
        'scp-consent-core',
        SCP_PLUGIN_URL . 'public/js/consent-core.js',
        array( 'scp-event-queue', 'scp-integrations' ),
        SCP_VERSION,
        true
    );

    wp_localize_script( 'scp-consent-core', 'scpData', array(
        'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
        'nonce'       => wp_create_nonce( 'scp_nonce' ),
        'expiryDays'  => (int) get_option( 'scp_expiry_days', 180 ),
        'acceptLabel' => esc_html( get_option( 'scp_accept_label', 'Accept All' ) ),
        'rejectLabel' => esc_html( get_option( 'scp_reject_label', 'Reject All' ) ),
    ) );
}
