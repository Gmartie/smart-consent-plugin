<?php
/**
 * Plugin Name: Smart Consent Plugin
 * Plugin URI:  https://example.com/smart-consent-plugin
 * Description: A GDPR-compliant consent management plugin with event queuing and third-party integrations.
 * Version:     1.0.0
 * Author:      Your Name
 * Author URI:  https://example.com
 * License:     GPL-2.0+
 * Text Domain: smart-consent-plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SCP_VERSION', '1.0.0' );
define( 'SCP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SCP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once SCP_PLUGIN_DIR . 'includes/enqueue.php';
require_once SCP_PLUGIN_DIR . 'includes/consent.php';
require_once SCP_PLUGIN_DIR . 'includes/ajax.php';

if ( is_admin() ) {
    require_once SCP_PLUGIN_DIR . 'admin/settings-page.php';
}

register_activation_hook( __FILE__, 'scp_activate' );
register_deactivation_hook( __FILE__, 'scp_deactivate' );

function scp_activate() {
    $defaults = array(
        'scp_banner_title'   => __( 'We value your privacy', 'smart-consent-plugin' ),
        'scp_banner_message' => __( 'We use cookies to enhance your browsing experience.', 'smart-consent-plugin' ),
        'scp_accept_label'   => __( 'Accept All', 'smart-consent-plugin' ),
        'scp_reject_label'   => __( 'Reject All', 'smart-consent-plugin' ),
        'scp_expiry_days'    => 180,
    );
    foreach ( $defaults as $key => $value ) {
        if ( false === get_option( $key ) ) {
            add_option( $key, $value );
        }
    }
}

function scp_deactivate() {
    // Cleanup tasks if needed.
}
