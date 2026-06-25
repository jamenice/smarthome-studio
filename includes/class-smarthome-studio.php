<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://themeforest.net/user/jwsthemes
 * @since      1.0.0
 *
 * @package    Smarthome_Studio
 * @subpackage Smarthome_Studio/includes
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
 * @package    Smarthome_Studio
 * @subpackage Smarthome_Studio/includes
 * @author     Waqas Riaz <waqas@jwsthemes.com>
 */

namespace SmarthomeStudio;
use Elementor;


class Smarthome_Studio {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Smarthome_Studio_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * Instance of Elemenntor Frontend class.
	 *
	 * @var \Elementor\Frontend()
	 */
	private static $elementor_instance;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The minimum Elementor version number required.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var string
	 */
	public static $minimum_elementor_version = '3.13.0';

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
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
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'JWS_VERSION' ) ) {
			$this->version = JWS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'smarthome-studio';

		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), -1 );

		add_action( 'init', array( $this, 'initialize_plugin' ) );


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
	 * - Smarthome_Studio_Loader. Orchestrates the hooks of the plugin.
	 * - Smarthome_Studio_i18n. Defines internationalization functionality.
	 * - Smarthome_Studio_Admin. Defines all hooks for the admin area.
	 * - Smarthome_Studio_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		// Load WPML & Polylang Compatibility if WPML is installed and activated.
		if ( defined( 'ICL_SITEPRESS_VERSION' ) || defined( 'POLYLANG_BASENAME' ) ) {
			require_once JWS_DIR_PATH . 'includes/compatibility/class-smarthome-studio-wpml-compatibility.php';
		}

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once JWS_DIR_PATH . 'includes/class-smarthome-render-template.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once JWS_DIR_PATH . 'includes/class-smarthome-studio-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once JWS_DIR_PATH . 'includes/class-smarthome-studio-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		
		require_once JWS_DIR_PATH . 'admin/class-smarthome-studio-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once JWS_DIR_PATH . 'public/class-smarthome-studio-public.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once JWS_DIR_PATH . 'elementor/class-smarthome-elementor.php';

		/**
		 * The class responsible change query
		 */
		//require_once JWS_DIR_PATH . 'includes/class-smarthome-query-switcher.php';

		/**
		 * Helper functions
		 * core plugin.
		 */
		require_once JWS_DIR_PATH . 'includes/helpers.php';

		$this->loader = new Smarthome_Studio_Loader();

	}

	public function initialize_plugin() {

		$is_elementor_installed = ( defined( 'ELEMENTOR_VERSION' ) && is_callable( 'Elementor\Plugin::instance' ) ) ? true : false;

		$is_elementor_outdated = ( $is_elementor_installed && ( ! version_compare( ELEMENTOR_VERSION, self::$minimum_elementor_version, '>=' ) ) ) ? true : false;

		if ( ( ! $is_elementor_installed ) || $is_elementor_outdated ) {
			$this->check_elementor_version( $is_elementor_installed, $is_elementor_outdated );
		}
		
		if( $is_elementor_installed ) {
			self::$elementor_instance = Elementor\Plugin::instance();
		}

	}

	/**
	 * Prints the admin notics when Elementor is not installed or activated or version outdated.
	 *
	 * @since 1.0.0
	 */
	public function check_elementor_version( $is_elementor_installed, $is_elementor_outdated ) {


		if ( ( ! did_action( 'elementor/loaded' ) ) || ( ! $is_elementor_installed ) ) {
			add_action( 'admin_notices', [ $this, 'elementor_installion_notice' ] );
			add_action( 'network_admin_notices', [ $this, 'elementor_installion_notice' ] );
			return;
		}

		// Check for the minimum required Elementor version.
		if ( $is_elementor_outdated ) {
			if ( current_user_can( 'update_plugins' ) ) {
				add_action( 'admin_notices',
				[ $this, 'elementor_version_notice' ] );
				add_action( 'network_admin_notices', [ $this, 'elementor_installion_notice' ] );
			}
			return;
		}

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Smarthome_Studio_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Smarthome_Studio_i18n();

		$this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Smarthome_Studio_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Smarthome_Studio_Public( $this->get_plugin_name(), $this->get_version() );
		

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Admin notice if elementor plugin not installed or activated
	 */
	public function elementor_installion_notice() {

		$screen = get_current_screen();
		if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
			return;
		}

		if ( ! did_action( 'elementor/loaded' ) ) {
			
			if ( ! ( current_user_can( 'activate_plugins' ) && current_user_can( 'install_plugins' ) ) ) {
				return;
			}

			$class = 'notice notice-error';
			
			$message = sprintf( __( 'The %1$sSmarthome Studio%2$s plugin requires %1$sElementor%2$s plugin installed & activated.', 'smarthome-studio' ), '<strong>', '</strong>' );

			$plugin = 'elementor/elementor.php';

			if ( self::jws_studio_is_elementor_installed() ) {

				$action_url   = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
				$button_label = __( 'Activate Elementor', 'smarthome-studio' );

			} else {

				$action_url   = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
				$button_label = __( 'Install Elementor', 'smarthome-studio' );
			}

			$button = '<p><a href="' . esc_url( $action_url ) . '" class="button-primary">' . esc_html( $button_label ) . '</a></p><p></p>';

			printf( '<div class="%1$s"><p>%2$s</p>%3$s</div>', esc_attr( $class ), wp_kses_post( $message ), wp_kses_post( $button ) );
		}
	}

	/**
	 * Admin notice when elementor plugin out of date
	 */
	public function elementor_version_notice() {

		if ( ! ( current_user_can( 'activate_plugins' ) && current_user_can( 'install_plugins' ) ) ) {
			return;
		}

		$class = 'notice notice-error';
		$message = sprintf( __( 'The %1$sSmarthome Studio%2$s plugin has stopped working because you are using an older version of %1$sElementor%2$s plugin.', 'smarthome-studio' ), '<strong>', '</strong>' );

		$plugin = 'elementor/elementor.php';

		if ( self::jws_studio_is_elementor_installed() ) {

			$action_url   = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&amp;plugin=' ) . $plugin . '&amp;', 'upgrade-plugin_' . $plugin );
			$button_label = __( 'Update Elementor', 'smarthome-studio' );

		} else {

			$action_url   = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
			$button_label = __( 'Install Elementor', 'smarthome-studio' );
		}

		$button = '<p><a href="' . esc_url( $action_url ) . '" class="button-primary">' . esc_html( $button_label ) . '</a></p><p></p>';

		printf( '<div class="%1$s"><p>%2$s</p>%3$s</div>', esc_attr( $class ), wp_kses_post( $message ), wp_kses_post( $button ) );
	}


	/**
	 * Check if elementor plugin installed
	 */
	public static function jws_studio_is_elementor_installed() {

		return ( file_exists( WP_PLUGIN_DIR . '/elementor/elementor.php' ) ) ? true : false;
	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
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
	 * @return    Smarthome_Studio_Loader    Orchestrates the hooks of the plugin.
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

	/**
	 * Returns true if the request is a non-legacy REST API request.
	 *
	 * Legacy REST requests should still run some extra code for backwards compatibility.
	 *
	 * @todo: replace this function once core WP function is available: https://core.trac.wordpress.org/ticket/42061.
	 *
	 * @return bool
	 */
	public function is_rest_api_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$rest_prefix         = trailingslashit( rest_get_url_prefix() );
		$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		return apply_filters( 'jws_is_rest_api_request', $is_rest_api_request );
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! $this->is_rest_api_request();
		}
	}

	/**
	 * When WP has loaded all plugins, trigger the `Smarthome_studio_loaded` hook.
	 *
	 * This ensures `Smarthome_studio_loaded` is called only after all other plugins
	 * are loaded, to avoid issues caused by plugin directory naming changing
	 * the load order.
	 *
	 * @since 1.0.0
	 */
	public function on_plugins_loaded() {
		do_action( 'jws_loaded' );
	}

}
