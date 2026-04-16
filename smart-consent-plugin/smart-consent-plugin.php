<?php
/*
Plugin Name: Smart Consent Tracker
Description: Consentimiento avanzado + recuperación de eventos
Version: 1.0
*/

if (!defined('ABSPATH')) exit;

// Cargar scripts front
require_once plugin_dir_path(__FILE__) . 'includes/enqueue.php';

// Mostrar banner
require_once plugin_dir_path(__FILE__) . 'includes/consent.php';

// AJAX
require_once plugin_dir_path(__FILE__) . 'includes/ajax.php';

require_once plugin_dir_path(__FILE__) . 'admin/settings-page.php';