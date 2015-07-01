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
		 * Pluglin Slug
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
			load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Add Menu Page
		 * 
		 * @return void
		 */
		public function add_menu_page() {
			add_menu_page( 'WP API JSON Import', 'WP API JSON Import', 'manage_options', $this->plugin_slug, array( $this, 'import_posts' ), '', 6 );
		}

		/**
		 * Page menu import posts
		 */
		public function import_posts() {
			echo '<div class="wrap">';
				echo '<h2>' . __( 'WP API JSON Import', $this->plugin_slug ) . '</h2>';
			echo '</div>';
		}
	}

	add_action( 'plugins_loaded', array( 'WP_API_JSON_Import', 'get_instance' ), 0 );




























