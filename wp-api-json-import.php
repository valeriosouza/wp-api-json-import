<?php
	/**
	 * Plugin Name: WP API JSON Import
	 * Plugin URI:
	 * Description:
	 * Author: valeriosza, leobaiano, nicholas_io
	 * Author URI:
	 * Version: 0.0.2
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
		const VERSION = '0.0.2';

		/**
		 * Plugin Slug
		 * @var string
		 */
		public static $plugin_slug = 'wpapijson-import';

		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * Holds the name of the settings section
		 *
		 * @var string
		 */
		protected $settings_section;

		/**
		 * Holds the array of saved options
		 *
		 * @var string
		 */
		protected $options = array();

		/**
		 * Holds options name under settings api
		 *
		 * @var string
		 */
		protected $option_name;

		/**
		 * Initialize the plugin
		 */
		private function __construct() {
			// Load plugin text domain
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

			// Load scripts js and styles css
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Add menu page
			add_action( 'admin_menu', array( $this, 'add_menu_page') );

			// Add Register
			add_action( 'admin_init', array( $this, 'plugin_options') );

			// Run Import
			add_action( 'my_hourly_event', array( $this, 'import_posts') );

			// Function AJAX impot posts
    		//add_action( 'wp_ajax_import_posts', array( $this, 'import_posts' ) );
    		add_action( 'wp_ajax_nopriv_import_posts', array( $this, 'import_posts' ) );

    		$this->settings_section = self::$plugin_slug . '-general-section';
    		$this->option_name 		= self::$plugin_slug . '-settings';
    		$this->options 			= get_option( $this->option_name );

    		if ( $this->options === false ) {
    			$this->options = array();
    		}

    		$this->options = wp_parse_args(
    			$this->options,
    			array(
    				'urls' => ''
    			)
    		);


    		// Load Importer API
			//require_once ABSPATH . 'wp-admin/includes/import.php';
			//$GLOBALS['wp_rest_import'] = new WP_API_JSON_Import();
    		//register_importer('wpapijsonimport', __('WP API JSON Import', 'wpapijson-import'), __('Import links in OPML format.', 'wpapijson-import'), array($GLOBALS['wp_rest_import'], 'dispatch'));
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
		 * Load scripts js and styles css
		 */
		public function enqueue_scripts() {
			// variables for main.js
			$params = array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ) );

			// Load main CSS file
			wp_enqueue_style( self::$plugin_slug . '_css_main', plugins_url( 'assets/css/main.css', __FILE__ ), array(), null, 'all' );

			// Load main JS file
			wp_enqueue_script( self::$plugin_slug . '_js_main', plugins_url( 'assets/js/main.js', __FILE__ ), array( 'jquery' ), null, true );

			// WP Localize Script pass variables for main.js
			wp_localize_script( self::$plugin_slug . '_js_main', 'sale_post_variables', $params );
		}

		/**
		 * Fired when the plugin is activated.
		 *
		 * @param  boolean $network_wide True if WPMU superadmin uses
		 *                               "Network Activate" action, false if
		 *                               WPMU is disabled or plugin is
		 *                               activated on an individual blog.
		 *
		 * @return void
		 */
		public static function activate( $network_wide ) {
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				if ( $network_wide  ) {

					// Get all blog ids
					$blog_ids = self::get_blog_ids();

					foreach ( $blog_ids as $blog_id ) {
						switch_to_blog( $blog_id );
						self::single_activate();
					}

					restore_current_blog();
				} else {
					self::single_activate();
				}
			} else {
				self::single_activate();
			}
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

			if ( get_option('wpapijson-import_urls') !== false ) {
				update_option( 'wpapijson-import_urls',  array() );
			}

			update_option( 'wpapijson_import_version', self::VERSION );
		}

		/**
		 * Add Menu Page
		 *
		 * @return void
		 */
		public function add_menu_page() {
			add_management_page(
				'WP API JSON Import',
				'WP API JSON Import',
				'manage_options',
				self::$plugin_slug,
				array( $this, 'import_posts_view' ),
				'',
				6
			);
		}

		/**
		 * Page menu import posts
		 */
		public function import_posts_view() {
			echo '<div class="wrap">';
				echo '<h2>' . esc_html( get_admin_page_title() ) . '</h2>';
				echo '<form action="options.php" method="post">';
					settings_fields( $this->option_name );
					do_settings_sections( self::$plugin_slug );
					submit_button();
				echo '</form>';

				echo '<div class="' . self::$plugin_slug . '_wraper_posts_imports">';
					echo '<hr />';
					echo '<h3>' . __( 'Posts Imported', self::$plugin_slug ) . '</h3>';
					echo '<div class="' . self::$plugin_slug . '_posts_import"></div>';
				echo '</div>';
			echo '</div>';
		}

		/**
		 *	Defines the settings fo the plugins
		 */
		public function plugin_options() {
			if ( false == get_option( $this->option_name ) ) {
				add_option( $this->option_name );
			}

			add_settings_section(
				$this->settings_section,
				__( 'Main Settings', self::$plugin_slug),
				array( $this, 'general_settings_section' ),
				self::$plugin_slug
			);

			add_settings_field(
				'urls',
				__( "URL's", self::$plugin_slug ),
				array( $this, 'urls_field' ),
				self::$plugin_slug,
				$this->settings_section
			);

 			register_setting(
 				$this->option_name,
 				$this->option_name
 			);
		}

		public function general_settings_section( $args ) {
			//echo __( '<p>General Settings</p>', self::$plugin_slug );
		}

		public function urls_field( $args ) {
			?>
			<textarea class="widefat" id="urls" rows="7" name="<?php echo $this->option_name; ?>[urls]"><?php echo $this->options['urls']; ?></textarea>
			<label for="urls"><p>  <?php _e( 'Enter the url\'s, separated by commas in order to import.', self::$plugin_slug ) ?> </p></label>
			<?php
		}

		/**
		 * Import Posts JSON
		 */
		public function import_posts() {
			$return = '';
			$message = '';
			//Teste de funcao
			wp_mail( 'valeriosza@gmail.com', 'run', 'rodou');
			// Get and sanitize data input
			$urls = sanitize_text_field( $this->options['url'] );
			// Get json and converte array
			$posts = json_decode( file_get_contents( $urls ), true );

			// iterator count posts
			$i = 0;

			// loop in posts return
			if( count( $posts ) ){
				foreach( $posts as $post ) {
					if ( $i < 2 ) {
						$return .= '<p>' . $post['title'] . '</p>';
					}
					$i++;
				}
			}

			if ( empty( $return ) )
				$message = 'No post found';

			$response = array( 'status' => 1, 'message' => $return );
			echo json_encode( $response );
			echo "string";
			die();
		}

		public static function schedule() {
			wp_schedule_event(time(), 'hourly', 'my_hourly_event');
		}
	}

	// Activate plugin when new blog is added.
	register_activation_hook( __FILE__ , array( 'WP_API_JSON_Import', 'activate' ) );
	register_activation_hook( __FILE__ , array( 'WP_API_JSON_Import', 'schedule' ));

	add_action( 'plugins_loaded', array( 'WP_API_JSON_Import', 'get_instance' ), 0 );
