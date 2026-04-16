<?php
/**
 * Template: Consent Banner
 *
 * Rendered in wp_footer. Visibility is controlled by consent-core.js.
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$title   = esc_html( get_option( 'scp_banner_title',   __( 'We value your privacy', 'smart-consent-plugin' ) ) );
$message = esc_html( get_option( 'scp_banner_message', __( 'We use cookies to enhance your browsing experience, serve personalised content, and analyse our traffic. By clicking "Accept All" you consent to our use of cookies.', 'smart-consent-plugin' ) ) );
$accept  = esc_html( get_option( 'scp_accept_label',   __( 'Accept All', 'smart-consent-plugin' ) ) );
$reject  = esc_html( get_option( 'scp_reject_label',   __( 'Reject All', 'smart-consent-plugin' ) ) );
?>
<div id="scp-consent-banner" role="dialog" aria-live="polite" aria-hidden="true" aria-label="<?php esc_attr_e( 'Cookie consent', 'smart-consent-plugin' ); ?>">
    <div class="scp-banner-inner">
        <div class="scp-banner-content">
            <h2 class="scp-banner-title"><?php echo $title; ?></h2>
            <p class="scp-banner-message"><?php echo $message; ?></p>
        </div>
        <div class="scp-banner-actions">
            <button id="scp-reject-btn" class="scp-btn scp-btn-reject" type="button">
                <?php echo $reject; ?>
            </button>
            <button id="scp-accept-btn" class="scp-btn scp-btn-accept" type="button">
                <?php echo $accept; ?>
            </button>
        </div>
    </div>
</div>
