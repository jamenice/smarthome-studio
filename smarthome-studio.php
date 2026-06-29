<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function 
 * that starts the plugin.
 *
 * @link              https://themeforest.net/user/jwsthemes
 * @since             1.0.0
 * @package           Goyard_Studio
 *
 * @wordpress-plugin
 * Plugin Name:       Goyard Studio
 * Plugin URI:        https://studio.smarthome.co
 * Description:       Add header, footer, menu builder for jwsthemes themes
 * Version:           1.2.0
 * Author:            Jwsthemes, Waqas Riaz
 * Author URI:        https://themeforest.net/user/jwsthemes/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       goyard-studio
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
if( !defined( 'JWS_VERSION' ) ) {
define( 'JWS_VERSION', '1.2.0' );
}
define( 'JWS_NOTICE_MIN_PHP_VERSION', '7.0' );
define( 'JWS_NOTICE_MIN_WP_VERSION', '6.0' );
define( 'JWS_DELIMITER', '|' );

define( 'JWS_FILE', __FILE__ );
define( 'JWS_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'JWS_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'JWS_PHP_MIN_REQUIREMENTS_NOTICE', 'wp_php_min_requirements_' . JWS_NOTICE_MIN_PHP_VERSION . '_' . JWS_NOTICE_MIN_WP_VERSION );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-smarthome-studio-activator.php
 */
function activate_jws() {
	require_once JWS_DIR_PATH . 'includes/class-smarthome-studio-activator.php';
	SmarthomeStudio\Smarthome_Studio_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-smarthome-studio-deactivator.php
 */
function deactivate_jws() {
	require_once JWS_DIR_PATH . 'includes/class-smarthome-studio-deactivator.php';
	SmarthomeStudio\Smarthome_Studio_Deactivator::deactivate();
}

register_activation_hook( JWS_FILE, 'activate_jws' );
register_deactivation_hook( JWS_FILE, 'deactivate_jws' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require JWS_DIR_PATH . 'includes/class-smarthome-studio.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_smarthome_studio() {

	$plugin = new SmarthomeStudio\Smarthome_Studio();
	$plugin->run();

}
run_smarthome_studio();
