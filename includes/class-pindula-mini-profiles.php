<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.controvert.co/
 * @since      1.0.0
 *
 * @package    Pindula_Mini_Profiles
 * @subpackage Pindula_Mini_Profiles/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Pindula_Mini_Profiles
 * @subpackage Pindula_Mini_Profiles/includes
 * @author     Controvert <support@controvert.co>
 */
class Pindula_Mini_Profiles {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    2.50
	 * @access   protected
	 * @var      Pindula_Mini_Profiles_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    2.50
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    2.50
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    2.50
	 */
	public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'pindula-mini-profiles';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Pindula_Mini_Profiles_Loader. Orchestrates the hooks of the plugin.
	 * - Pindula_Mini_Profiles_i18n. Defines internationalization functionality.
	 * - Pindula_Mini_Profiles_Admin. Defines all hooks for the admin area.
	 * - Pindula_Mini_Profiles_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    2.50
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pindula-mini-profiles-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pindula-mini-profiles-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-pindula-mini-profiles-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-pindula-mini-profiles-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pindula-mini-profiles-helper.php';

		$this->loader = new Pindula_Mini_Profiles_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Pindula_Mini_Profiles_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    2.50
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Pindula_Mini_Profiles_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    2.50
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Pindula_Mini_Profiles_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_helper_functions = new Helper_Functions();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'pindula_mini_profiles_admin_menu' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'pindula_mini_profiles_add_custom_box' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'pindula_mini_profiles_save_postmeta' );
		/*
		 * the data loaded by this request i.e Profile Titles is only required when editing posts
		 * This data is therefore only required in admin side
		*/	
		$this->loader->add_action( 'wp_ajax_p_mini_ajax_titles_refresh', $plugin_helper_functions, 'p_mini_ajax_titles_refresh');
		$this->loader->add_action( 'wp_footer', $plugin_helper_functions, 'p_mini_enable_frontend_ajax');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    2.50
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Pindula_Mini_Profiles_Public( $this->get_plugin_name(), $this->get_version() );
		$plugin_helper_functions = new Helper_Functions();

		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'pindula_mini_profiles_add_custom_rest_field' );
		$this->loader->add_action( 'pre_get_posts', $plugin_public, 'should_display_pindula_mini_snippet' );	
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    2.50
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Pindula_Mini_Profiles_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
