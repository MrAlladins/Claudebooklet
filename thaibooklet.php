<?php
/**
 * Plugin Name: Thaibooklet Manager
 * Plugin URI: https://yourwebsite.com/thaibooklet
 * Description: Ett system för att hantera kupongboklets i både fysiskt och digitalt format
 * Version: 1.0.0
 * Author: Din Utvecklare
 * Author URI: https://dinwebsite.com
 * Text Domain: thaibooklet
 * Domain Path: /languages
 */

// Om denna fil anropas direkt, avbryt.
if (!defined('WPINC')) {
    die;
}

// Definiera plugin-konstanter
define('THAIBOOKLET_VERSION', '1.0.0');
define('THAIBOOKLET_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('THAIBOOKLET_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Kod som körs vid aktivering av pluginen
 */
function activate_thaibooklet() {
    require_once THAIBOOKLET_PLUGIN_DIR . 'includes/class-thaibooklet-activator.php';
    Thaibooklet_Activator::activate();
}

/**
 * Kod som körs vid avaktivering av pluginen
 */
function deactivate_thaibooklet() {
    require_once THAIBOOKLET_PLUGIN_DIR . 'includes/class-thaibooklet-deactivator.php';
    Thaibooklet_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_thaibooklet');
register_deactivation_hook(__FILE__, 'deactivate_thaibooklet');

/**
 * Huvudklassen som driver pluginen
 */
require THAIBOOKLET_PLUGIN_DIR . 'includes/class-thaibooklet.php';

/**
 * Börjar köra pluginen
 */
function run_thaibooklet() {
    $plugin = new Thaibooklet();
    $plugin->run();
}

run_thaibooklet();