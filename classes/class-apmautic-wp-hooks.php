<?php
/**
 * Core Actions, Post type
 *
 * @package automate-mautic
 * @since 1.0.5
 */

/**
 * Create class APMautic_WP_Hooks
 * Handles register post type, trigger actions
 *
 * @package automate-mautic
 * @since 1.0.5
 */
class APMautic_WP_Hooks {

	/**
	 * Declare a static variable instance.
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Initiate class
	 *
	 * @since 1.0.0
	 * @return object
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) ) {
			self::$instance = new APMautic_WP_Hooks();
			self::$instance->hooks();
		}

		return self::$instance;
	}

	/**
	 * Call hooks
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function hooks() {
		add_action( 'wp_head', __CLASS__ . '::mautic_tracking_script' );
		add_action( 'init', __CLASS__ . '::mautic_register_posttype' );
		add_action( 'admin_init', __CLASS__ . '::update_access_token' );
		add_action( 'plugins_loaded', __CLASS__ . '::load_plugin_textdomain' );
	}

	/**
	 * Update access token after Authentication
	 *
	 * @since 1.0.5
	 * @return void
	 */
	public static function update_access_token() {
		$instance = APMautic_Services::get_service_instance( AP_MAUTIC_SERVICE );
		$instance->update_token();
	}

	/**
	 * Load text domain
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function load_plugin_textdomain() {
		load_plugin_textdomain( 'automate-mautic' );
	}

	/**
	 * Writes Mautic Tracking JS to the HTML source of WP head
	 *
	 * @since 1.0.0
	 */
	public static function mautic_tracking_script() {

		if ( 1 == apm_get_option( 'enable-tracking', 1 ) ) {
			$enable_mautic_tracking = true;
		} else {
			$enable_mautic_tracking = false;
		}
		$base_url = apm_get_option( 'base-url' );

		if ( $enable_mautic_tracking && ! empty( $base_url ) ) {

			$base_url = esc_url( trim( $base_url, " \t\n\r\0\x0B/" ) );

			$js_tracking = "<script>
			(function(w,d,t,u,n,a,m){w['MauticTrackingObject']=n;
			w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),
			m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)
			})(window,document,'script','{$base_url}/mtc.js','mt');
			mt('send', 'pageview');
			</script>";
			echo $js_tracking;
		}
	}

	/**
	 * Register a automate-mautic post type.
	 *
	 * @since 1.0.0
	 * @link http://codex.wordpress.org/Function_Reference/register_post_type
	 */
	public static function mautic_register_posttype() {
		$labels = array(
			'name'               => _x( 'Rules', 'post type general name', 'automate-mautic' ),
			'singular_name'      => _x( 'Rule', 'post type singular name', 'automate-mautic' ),
			'menu_name'          => _x( 'Rules', 'admin menu', 'automate-mautic' ),
			'name_admin_bar'     => _x( 'Rule', 'add new on admin bar', 'automate-mautic' ),
			'add_new'            => _x( 'Add New', 'rule', 'automate-mautic' ),
			'add_new_item'       => __( 'Add New Rule', 'automate-mautic' ),
			'new_item'           => __( 'New Rule', 'automate-mautic' ),
			'edit_item'          => __( 'Edit Rule', 'automate-mautic' ),
			'view_item'          => __( 'View Rule', 'automate-mautic' ),
			'all_items'          => __( 'All Rules', 'automate-mautic' ),
			'search_items'       => __( 'Search Rules', 'automate-mautic' ),
			'parent_item_colon'  => __( 'Parent Rules:', 'automate-mautic' ),
			'not_found'          => __( 'No rules found.', 'automate-mautic' ),
			'not_found_in_trash' => __( 'No rules found in Trash.', 'automate-mautic' ),
		);
		$args   = array(
			'labels'             => $labels,
			'description'        => __( 'Description.', 'automate-mautic' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => ! ( apm_get_option( 'apmautic_menu_position' ) ) ? 'options-general.php' : apm_get_option( 'apmautic_menu_position' ),
			'query_var'          => true,
			'rewrite'            => array(
				'slug' => AP_MAUTIC_POSTTYPE,
			),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'          => 'dashicons-chart-line',
			'supports'           => array( 'title' ),
		);
		register_post_type( AP_MAUTIC_POSTTYPE, $args );
	}
}
APMautic_WP_Hooks::instance();
