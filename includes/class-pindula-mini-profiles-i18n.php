<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://www.controvert.co/
 * @since      1.0.0
 *
 * @package    Pindula_Mini_Profiles
 * @subpackage Pindula_Mini_Profiles/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Pindula_Mini_Profiles
 * @subpackage Pindula_Mini_Profiles/includes
 * @author     Controvert <support@controvert.co>
 */
class Pindula_Mini_Profiles_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    2.50
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'pindula-mini-profiles',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
