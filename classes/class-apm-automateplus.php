<?php
/**
 * Mautic for WordPress initiate
 *
 * @package automateplus-mautic
 * @since 1.0.0
 */

if ( ! class_exists( 'AutomatePlus_Mautic' ) ) :

	/**
	 * Create class AutomatePlus_Mautic
	 * Handles register post type, trigger actions
	 */
	class AutomatePlus_Mautic {

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
				self::$instance = new AutomatePlus_Mautic();
				self::$instance->includes();
				self::$instance->hooks();
			}
			return self::$instance;
		}

		/**
		 * Include files required to plugin
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function includes() {

			require_once( AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'classes/class-apm-init.php' );
			require_once( AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'classes/class-apm-mautic-api.php' );
			require_once( AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'classes/class-apm-rulepanel.php' );
		}

		/**
		 * Call hooks
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function hooks() {
			add_action( 'init', array( $this, 'mautic_register_posttype' ) );
			add_action( 'wp_head', array( $this, 'mautic_tracking_script' ) );
			add_action( 'user_register', array( $this, 'add_registered_user' ), 10, 1 );
			add_action( 'profile_update', array( $this, 'add_registered_user' ), 10, 1 );
			add_action( 'comment_post', array( $this, 'add_comment_author' ), 10, 3 );
			add_filter( 'update_footer', array( $this, 'refresh_edit_text' ), 999 );
		}

		/**
		 * Writes Mautic Tracking JS to the HTML source of WP head
		 *
		 * @since 1.0.0
		 */
		public function mautic_tracking_script() {

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
		 * Add button to refresh Mautic data in footer
		 *
		 * @since 1.0.0
		 * @param string $footer_text default footer text.
		 * @return string
		 */
		public function refresh_edit_text( $footer_text ) {

			$screen = get_current_screen();

			if ( 'settings_page_automate-mautic' == $screen->id ) {
				$refresh_text = '<span class="spinner amp_footer_spinner"></span>';
				$refresh_text .= __( '<a type="button" name="refresh-mautic" id="refresh-mautic" class="refresh-mautic-data"> Refresh Mautic Data</a>' );
				$footer_text  = $refresh_text . ' | ' . $footer_text;
			}

			return $footer_text;
		}

		/**
		 * Register a automate-mautic post type.
		 *
		 * @since 1.0.0
		 * @link http://codex.wordpress.org/Function_Reference/register_post_type
		 */
		public function mautic_register_posttype() {
			$labels = array(
				'name'               => _x( 'Rules', 'post type general name', 'automateplus-mautic-wp' ),
				'singular_name'      => _x( 'Rule', 'post type singular name', 'automateplus-mautic-wp' ),
				'menu_name'          => _x( 'Rules', 'admin menu', 'automateplus-mautic-wp' ),
				'name_admin_bar'     => _x( 'Rule', 'add new on admin bar', 'automateplus-mautic-wp' ),
				'add_new'            => _x( 'Add New', 'rule', 'automateplus-mautic-wp' ),
				'add_new_item'       => __( 'Add New Rule', 'automateplus-mautic-wp' ),
				'new_item'           => __( 'New Rule', 'automateplus-mautic-wp' ),
				'edit_item'          => __( 'Edit Rule', 'automateplus-mautic-wp' ),
				'view_item'          => __( 'View Rule', 'automateplus-mautic-wp' ),
				'all_items'          => __( 'All Rules', 'automateplus-mautic-wp' ),
				'search_items'       => __( 'Search Rules', 'automateplus-mautic-wp' ),
				'parent_item_colon'  => __( 'Parent Rules:', 'automateplus-mautic-wp' ),
				'not_found'          => __( 'No rules found.', 'automateplus-mautic-wp' ),
				'not_found_in_trash' => __( 'No rules found in Trash.', 'automateplus-mautic-wp' ),
			);
			$args = array(
				'labels'             => $labels,
				'description'        => __( 'Description.', 'automateplus-mautic-wp' ),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => false,
				'show_in_menu'       => 'options-general.php',
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'automate-mautic' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'menu_icon'			 => 'dashicons-chart-line',
				'supports'           => array( 'title' ),
			);
			register_post_type( 'automate-mautic', $args );
		}

		/**
		 * Add registered WP users to Mautic contacts
		 *
		 * @since 1.0.0
		 * @param int $user_id WP Users unique ID.
		 * @return void
		 */
		public function add_registered_user( $user_id ) {

			// return if $user_id is not available.
			if ( ! $user_id ) {

				return;
			}
			$all_tags = '';

			// get user registerd condition rules.
			$status = APM_RulePanel::get_wpur_condition();

			// return if the $status is not as expected.
			if ( ! is_array( $status ) || 0 == sizeof( $status ) ) {
				return;
			}

			$set_actions = APM_RulePanel::get_all_actions( $status );

			$user_info = get_userdata( $user_id );

			$email = $user_info->user_email;

			$body = array(
				'firstname'	=> $user_info->first_name,
				'lastname'	=> $user_info->last_name,
				'email'		=> $user_info->user_email,
				'website'	=> $user_info->user_url,
			);

			$api_data = AP_Mautic_Api::get_api_method_url( $email );
			$url = $api_data['url'];
			$method = $api_data['method'];

				// add tags set in actions.
			if ( isset( $set_actions['add_tag'] ) ) {

				foreach ( $set_actions['add_tag'] as $tags ) {
					$all_tags .= $tags . ',';
				}

				$all_tags = rtrim( $all_tags ,',' );
				$body['tags'] = $all_tags;
			}

			AP_Mautic_Api::ampw_mautic_api_call( $url, $method, $body, $set_actions );
		}

		/**
		 * Add comments author to Mautic contacts
		 *
		 * @since 1.0.0
		 * @param int    $id comment author ID.
		 * @param string $approved Comment status.
		 * @param array  $commentdata Comment author data.
		 * @return void
		 */
		public function add_comment_author( $id, $approved, $commentdata ) {

			$all_tags = '';
			// get comment post condition rules.
			$status = APM_RulePanel::get_comment_condition( $commentdata );

			// return if the $status is not as expected.
			if ( ! is_array( $status ) || sizeof( $status ) == 0 ) {
				return;
			}

			$set_actions = APM_RulePanel::get_all_actions( $status );

			$email = $commentdata['comment_author_email'];

			$body = array(
				'firstname'	=> $commentdata['comment_author'],
				'email'		=> $commentdata['comment_author_email'],
				'website'	=> $commentdata['comment_author_url'],
			);

			$api_data = AP_Mautic_Api::get_api_method_url( $email );
			$url = $api_data['url'];
			$method = $api_data['method'];

				// add tags set in actions.
			if ( isset( $set_actions['add_tag'] ) ) {

				foreach ( $set_actions['add_tag'] as $tags ) {
					$all_tags .= $tags . ',';
				}

				$all_tags = rtrim( $all_tags ,',' );
				$body['tags'] = $all_tags;
			}

			AP_Mautic_Api::ampw_mautic_api_call( $url, $method, $body, $set_actions );
		}
	}
endif;
