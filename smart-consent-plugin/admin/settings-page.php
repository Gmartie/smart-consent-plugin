<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'scp_add_settings_page' );

function scp_add_settings_page() {
    add_options_page(
        __( 'Smart Consent Settings', 'smart-consent-plugin' ),
        __( 'Smart Consent', 'smart-consent-plugin' ),
        'manage_options',
        'smart-consent-plugin',
        'scp_render_settings_page'
    );
}

add_action( 'admin_init', 'scp_register_settings' );

function scp_register_settings() {
    register_setting( 'scp_settings_group', 'scp_banner_title',   array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'scp_settings_group', 'scp_banner_message', array( 'sanitize_callback' => 'sanitize_textarea_field' ) );
    register_setting( 'scp_settings_group', 'scp_accept_label',   array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'scp_settings_group', 'scp_reject_label',   array( 'sanitize_callback' => 'sanitize_text_field' ) );
    register_setting( 'scp_settings_group', 'scp_expiry_days',    array( 'sanitize_callback' => 'absint' ) );

    add_settings_section( 'scp_main_section', __( 'Banner Settings', 'smart-consent-plugin' ), '__return_false', 'smart-consent-plugin' );

    add_settings_field( 'scp_banner_title',   __( 'Banner Title', 'smart-consent-plugin' ),   'scp_field_banner_title',   'smart-consent-plugin', 'scp_main_section' );
    add_settings_field( 'scp_banner_message', __( 'Banner Message', 'smart-consent-plugin' ), 'scp_field_banner_message', 'smart-consent-plugin', 'scp_main_section' );
    add_settings_field( 'scp_accept_label',   __( 'Accept Button Label', 'smart-consent-plugin' ), 'scp_field_accept_label', 'smart-consent-plugin', 'scp_main_section' );
    add_settings_field( 'scp_reject_label',   __( 'Reject Button Label', 'smart-consent-plugin' ), 'scp_field_reject_label', 'smart-consent-plugin', 'scp_main_section' );
    add_settings_field( 'scp_expiry_days',    __( 'Cookie Expiry (days)', 'smart-consent-plugin' ), 'scp_field_expiry_days', 'smart-consent-plugin', 'scp_main_section' );
}

function scp_field_banner_title() {
    $value = esc_attr( get_option( 'scp_banner_title', '' ) );
    echo '<input type="text" name="scp_banner_title" value="' . $value . '" class="regular-text" />';
}

function scp_field_banner_message() {
    $value = esc_textarea( get_option( 'scp_banner_message', '' ) );
    echo '<textarea name="scp_banner_message" rows="4" class="large-text">' . $value . '</textarea>';
}

function scp_field_accept_label() {
    $value = esc_attr( get_option( 'scp_accept_label', 'Accept All' ) );
    echo '<input type="text" name="scp_accept_label" value="' . $value . '" class="regular-text" />';
}

function scp_field_reject_label() {
    $value = esc_attr( get_option( 'scp_reject_label', 'Reject All' ) );
    echo '<input type="text" name="scp_reject_label" value="' . $value . '" class="regular-text" />';
}

function scp_field_expiry_days() {
    $value = (int) get_option( 'scp_expiry_days', 180 );
    echo '<input type="number" name="scp_expiry_days" value="' . $value . '" min="1" max="365" class="small-text" />';
}

function scp_render_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Smart Consent Plugin Settings', 'smart-consent-plugin' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'scp_settings_group' );
            do_settings_sections( 'smart-consent-plugin' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
