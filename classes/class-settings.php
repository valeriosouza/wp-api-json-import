<?php

class WP_API_JSON_Import_Settings {
	/**
	 * Holds the name of the rls settings section
	 *
	 * @var string
	 */
	protected $urls_settings_section;

	/**
	 * Holds the name of the import settings secion
	 */
	protected $import_settings_section;

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
	 * Hoolds the Plugin Slug
	 *
	 * @var string
	 */
	protected $plugin_slug;


	public function __construct( $plugin_slug ) {
		$this->plugin_slug = $plugin_slug;

		// Add menu page
		add_action( 'admin_menu', array( $this, 'add_menu_page') );

		// Add Register
		add_action( 'admin_init', array( $this, 'plugin_options') );


		$this->urs_settings_section 	= $this->plugin_slug . '-general-section';
		$this->import_settings_section	= $this->plugin_slug . '-import-section';
		$this->option_name 				= $this->plugin_slug . '-settings';
		$this->options 					= get_option( $this->option_name );

		if ( $this->options === false ) {
			$this->options = array();
		}

		$this->options = wp_parse_args(
			$this->options,
			array(
				'urls' 	=> '',
				'posts' => 1,
				'cpt'	=> 0,
				'menus'	=> 0,
			)
		);
	}

	public function get_options() {
		return $this->options;
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
			$this->plugin_slug,
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
				do_settings_sections( $this->plugin_slug );
				submit_button();
			echo '</form>';

			echo '<div class="' . $this->plugin_slug . '_wraper_posts_imports">';
				echo '<hr />';
				echo '<h3>' . __( 'Posts Imported', $this->plugin_slug ) . '</h3>';
				echo '<div class="' . $this->plugin_slug . '_posts_import"></div>';
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
			$this->urls_settings_section,
			__( "URL's to Import", $this->plugin_slug ),
			array( $this, 'general_settings_section' ),
			$this->plugin_slug
		);

		add_settings_field(
			'urls',
			__( "URL's", $this->plugin_slug ),
			array( $this, 'urls_field' ),
			$this->plugin_slug,
			$this->urls_settings_section
		);

		add_settings_section(
			$this->import_settings_section,
			__( "Choose what to import", $this->plugin_slug ),
			array( $this, 'import_settings_section' ),
			$this->plugin_slug
		);

		$import_checkbox = array(
			array(
				'id' 		=> 'posts',
				'label' 	=> __( "Posts", $this->plugin_slug ),
				'callback' 	=> array( $this, 'posts_checkbox_field' )
			),
			array(
				'id'		=> 'cpt',
				'label'		=> __( "Custom Post Types", $this->plugin_slug ),
				'callback'	=> array( $this, 'cpt_checkbox_field' )
			),
			array(
				'id'		=> 'menus',
				'label'		=> __( "Menus", $this->plugin_slug ),
				'callback'	=> array( $this, 'menus_checkbox_field' )
			)
		);

		foreach( $import_checkbox as $import_option ) {
			add_settings_field(
				$import_option['id'],
				$import_option['label'],
				$import_option['callback'],
				$this->plugin_slug,
				$this->import_settings_section
			);
		}

		register_setting(
			$this->option_name,
			$this->option_name
		);
	}

	public function name( $option ) {
		echo esc_attr( $this->option_name . '[' . $option . ']' ) ;
	}

	public function id( $option ) {
		echo esc_attr( $this->plugin_slug . '-' . $option );
	}

	public function general_settings_section( $args ) {
		//echo __( '<p>General Settings</p>', $this->plugin_slug );
	}

	public function urls_field( $args ) {
		?>
		<textarea class="widefat" id="<?php $this->id( 'urls' ); ?>" rows="7" name="<?php $this->name( 'urls' ); ?>"><?php echo esc_textarea( $this->options['urls'] ); ?></textarea>
		<label for="<?php $this->id( 'urls' ); ?>">
			<p> <?php _e( 'Enter the url\'s, separated by commas in order to import.', $this->plugin_slug ) ?></p>
		</label>
		<?php
	}

	public function import_settings_section() {

	}

	public function posts_checkbox_field() {
		?>
		    <input type="checkbox" id="<?php $this->id( 'posts' ); ?>" name="<?php $this->name( 'posts' ); ?>" value="1" <?php checked(1, $this->options['posts'] ); ?>>
			<label for="<?php $this->id( 'posts' ); ?>">
				<?php _e( 'Check to import posts', $this->plugin_slug ); ?>
			</label>
		<?php
	}

	public function cpt_checkbox_field() {
		?>
		    <input type="checkbox" id="<?php $this->id( 'cpt' ); ?>" name="<?php $this->name( 'cpt' ); ?>"  value="1" <?php checked(1, $this->options['cpt'] ); ?>>
			<label for="<?php $this->id( 'cpt' ); ?>">
				<?php _e( 'Check to import Custom Post Types', $this->plugin_slug ); ?>
			</label>
		<?php
	}

	public function menus_checkbox_field() {
		?>
		    <input type="checkbox" id="<?php $this->id( 'menus' ); ?>" name="<?php $this->name( 'menus' ); ?>"  value="1" <?php checked(1, $this->options['menus'] ); ?>>
			<label for="<?php $this->id( 'menus' ); ?>">
				<?php _e( 'Check to import Menus', $this->plugin_slug ); ?>
			</label>
		<?php
	}

}
