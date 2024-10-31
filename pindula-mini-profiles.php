<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.controvert.co/
 * @since             2.50
 * @package           Pindula_Mini_Profiles
 *
 * @wordpress-plugin
 * Plugin Name:       Pindula Mini Profiles
 * Plugin URI:        news.pindula.co.zw
 * Description:       Do you want to automatically provide background information on the people and things you've written about in your article? "Pindula Mini Profiles" is focused on making reading more enjoyable for your readers by providing them contextual snippets so that they don't need to manually search the internet to understand the article's background.
 * Version:           2.52
 * Author:            Controvert
 * Author URI:        http://www.controvert.co/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pindula-mini-profiles
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'PLUGIN_NAME_VERSION', '2.52' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pindula-mini-profiles-activator.php
 */
function activate_pindula_mini_profiles() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pindula-mini-profiles-activator.php';
	Pindula_Mini_Profiles_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pindula-mini-profiles-deactivator.php
 */
function deactivate_pindula_mini_profiles() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pindula-mini-profiles-deactivator.php';
	Pindula_Mini_Profiles_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_pindula_mini_profiles' );
register_deactivation_hook( __FILE__, 'deactivate_pindula_mini_profiles' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pindula-mini-profiles.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.50
 */
function run_pindula_mini_profiles() {

	$plugin = new Pindula_Mini_Profiles();
	$plugin->run();

}
run_pindula_mini_profiles();
