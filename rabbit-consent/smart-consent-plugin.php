<?php
/**
 * Plugin Name: Rabbit Consent
 * Description: Gestión de consentimiento de cookies compatible con Google Consent Mode v2. Integra GTM y GA4 de forma nativa, sin dependencias externas.
 * Version: release
 * Author: Gabriel
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'includes/enqueue.php';
require_once plugin_dir_path(__FILE__) . 'includes/consent.php';
require_once plugin_dir_path(__FILE__) . 'includes/ajax.php';
require_once plugin_dir_path(__FILE__) . 'admin/settings-page.php';
