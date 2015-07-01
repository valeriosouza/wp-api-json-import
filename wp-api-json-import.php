<?php
	/**
	 * Plugin Name: WP API JSON Import
	 * Plugin URI:
	 * Description:
	 * Author: valeriosza, leobaiano
	 * Author URI: 
	 * Version: 0.0.1
	 * License: GPLv2 or later
	 * Text Domain: wpapijson-import
 	 * Domain Path: /languages/
	 */
	if ( ! defined( 'ABSPATH' ) )
		exit; // Exit if accessed directly.
	
	/**
	 * WP API JSON Import
	 *
	 * @author   Leo Baiano <leobaiano@lbideias.com.br>
	 */
	class WP_API_JSON_Import {

		/**
		 * Plugin version.
		 *
		 * @var string
		 */
		const VERSION = '1.0.0';

		/**
		 * Plugin Slug
		 * @var strng
		 */
		public static $plugin_slug = 'wpapijson-import';

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Initialize the plugin
		 */
		private function __construct() {
			// Load plugin text domain
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

			// Add menu page
			add_action( 'admin_menu', array( $this, 'add_menu_page') );
		}

		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Load the plugin text domain for translation.
		 *
		 * @return void
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( self::$plugin_slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Fired for each blog when the plugin is activated.
		 *
		 * @return   void
		 */

		private static function single_activate() {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}
			add_option( 'wpapijson_import', $options );
			add_option( 'wpapijson_import_version', self::VERSION );

		}

		/**
		 * Add Menu Page
		 * 
		 * @return void
		 */
		public function add_menu_page() {
			add_menu_page( 'WP API JSON Import', 'WP API JSON Import', 'manage_options', self::$plugin_slug, array( $this, 'import_posts' ), '', 6 );
		}

		/**
		 * Page menu import posts
		 */
		public function import_posts() {
			echo '<div class="wrap">';
				echo '<h2>' . __( 'WP API JSON Import', self::$plugin_slug ) . '</h2>';
				echo '<p>' . __( 'Enter the url\'s, separated by commas, to import.', self::$plugin_slug ) . '</p>';
				echo '<hr />';
				echo '<textarea name=" ' . self::$plugin_slug . '_urls" class="' . self::$plugin_slug . '_textarea"></textarea>';
				echo '<input type="submit" class="' . self::$plugin_slug . '_botao button button-primary button-large">';
			echo '</div>';
		}
	}

	add_action( 'plugins_loaded', array( 'WP_API_JSON_Import', 'get_instance' ), 0 );




























