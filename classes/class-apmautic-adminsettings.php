<?php
/**
 * Handles logic for the admin settings page.
 *
 * @package automate-mautic
 * @since 1.0.0
 */

if ( ! class_exists( 'APMautic_AdminSettings' ) ) :

	/**
	 * Create class APMautic_AdminSettings
	 * Handles settings page and post type table view
	 */
	final class APMautic_AdminSettings {

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
				self::$instance = new APMautic_AdminSettings();
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
			add_action( 'after_setup_theme', __CLASS__ . '::init_hooks' );
			add_action( 'admin_footer', __CLASS__ . '::mb_templates' );
			add_action( 'admin_notices', __CLASS__ . '::ap_mautic_notices', 100 );
			add_action( 'wp_loaded', __CLASS__ . '::access_capabilities', 1 );
			add_action( 'wp_loaded', __CLASS__ . '::mautic_authenticate_update' );
		}

		/**
		 * Include template to render meta box html
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function mb_templates() {
			$curr_screen = isset( $_REQUEST['page'] ) ? esc_attr( $_REQUEST['page'] ) : '';
			if ( AP_MAUTIC_POSTTYPE == $curr_screen ) {
				include AP_MAUTIC_PLUGIN_DIR . '/assets/templates/meta-box-template.php';
			}
		}

		/**
		 * Adds the admin menu and enqueues CSS/JS if we are on
		 * the MautiPress admin settings page.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function init_hooks() {
			add_action( 'admin_menu', __CLASS__ . '::menu' );
			if ( ! is_admin() ) {
				return;
			}
			$curr_screen = isset( $_REQUEST['page'] ) ? esc_attr( $_REQUEST['page'] ) : '';
			if ( AP_MAUTIC_POSTTYPE == $curr_screen ) {
				self::save();
				add_action( 'admin_enqueue_scripts', __CLASS__ . '::styles_scripts' );
			}
		}

		/**
		 * Renders the admin settings menu.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function menu() {
			if ( current_user_can( 'delete_users' ) ) {
				$func          = __CLASS__ . '::render';
				$menu_position = apm_get_option( 'apmautic_menu_position' );
				if ( ! class_exists( 'APMautic_Addon_Init' ) || ! $menu_position ) {
					add_options_page( 'AutomatePlug Mautic', __( 'AutomatePlug Mautic', 'automate-mautic' ), 'access_automate_mautic', AP_MAUTIC_POSTTYPE, $func );
				}
			}
		}

		/**
		 * Enqueues the needed CSS/JS for admin settings page.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function styles_scripts() {

			$curr_screen = isset( $_REQUEST['page'] ) ? esc_attr( $_REQUEST['page'] ) : '';
			if ( AP_MAUTIC_POSTTYPE == $curr_screen ) {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'apm-admin-script', AP_MAUTIC_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-sortable', 'wp-util' ) );
				wp_enqueue_style( 'apm-admin-style', AP_MAUTIC_PLUGIN_URL . 'assets/css/admin.css' );
				wp_enqueue_script( 'apm-select2-script', AP_MAUTIC_PLUGIN_URL . 'assets/js/select2.min.js', array( 'jquery' ) );
				wp_enqueue_style( 'apm-select2-style', AP_MAUTIC_PLUGIN_URL . 'assets/css/select2.min.css' );

				$options = array(
					'ajax_nonce' => wp_create_nonce( 'apm_mautic_admin_nonce' ),
				);
				wp_localize_script( 'apm-admin-script', 'ApmAdminScript', $options );
				do_action( 'amp_admin_scripts' );
			}
		}

		/**
		 * Renders the admin settings.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function render() {
			include AP_MAUTIC_PLUGIN_DIR . 'classes/class-apmautic-table.php';
			include AP_MAUTIC_PLUGIN_DIR . 'includes/admin-settings-main.php';
		}

		/**
		 * Display table.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function ampw_rules_list() {

			$new_post_url = APMautic_AdminSettings::get_render_page_url( '&tab=add_new_rule' );
			?>
			<div class="wrap">
				<?php
				if ( ! empty( $_GET['s'] ) ) {
					// translators: %&#8220: left double quotation mark.
					// translators: %&#8221: right double quotation mark.
					printf( '<span >' . __( 'Search results for &#8220;%s&#8221;', 'automate-mautic' ) . '</span>', esc_html( wp_unslash( $_GET['s'] ) ) );
				}
				?>
				<form method="get" action="" >

					<?php
					if ( isset( $_GET['page'] ) ) {
						echo '<input type="hidden" name="page" value="' . esc_attr( $_GET['page'] ) . '" />' . "\n";
					}
					$list_table = new APMautic_Table();
					$list_table->prepare_items();
					$list_table->search_box( 'search', 'apm_rule_search' );
					?>
				</form>
				<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="post">
					<input type="hidden" name="action" value="apm_rule_list" />
					<?php $list_table->display(); ?>
				</form>
			</div>
			<?php
		}

		/**
		 * Renders the admin settings page heading.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function render_page_heading() {
			$icon = AP_MAUTIC_PLUGIN_URL . '/assets/icon/mt.png';
			if ( ! empty( $icon ) ) {
				echo '<img class="apm-heading-icon" src="' . esc_url( $icon ) . '" />';
			}
			echo '<div class="amp-heading-config">' . __( 'AutomatePlug Mautic', 'automate-mautic' ) . '</div>';
		}

		/**
		 * Renders the update message.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function render_update_message() {

			if ( ! empty( $_POST ) ) {
				echo '<div class="updated"><p>' . __( 'Settings updated!', 'automate-mautic' ) . '</p></div>';
			}
		}

		/**
		 * Renders an admin settings form based on the type specified.
		 *
		 * @since 1.0.0
		 * @param string $type The type of form to render.
		 * @return void
		 */
		public static function render_form( $type ) {
			if ( self::has_support( $type ) ) {
				include AP_MAUTIC_PLUGIN_DIR . 'includes/admin-settings-' . $type . '.php';
			}
		}

		/**
		 * Return page url.
		 *
		 * @since 1.0.0
		 * @param string $type tab type.
		 */
		public static function render_page_url( $type = '' ) {
			$admin_url = self::get_render_page_url( $type );
			echo $admin_url;
		}

		/**
		 * Return page url.
		 *
		 * @since 1.0.0
		 * @param string $type tab type.
		 */
		public static function get_render_page_url( $type = '' ) {
			$parent    = self::get_menu_parent();
			$admin_url = admin_url( $parent );
			$admin_url = add_query_arg(
				array(
					'page' => 'automate-mautic' . $type,
				), $admin_url
			);
			return $admin_url;
		}

		/**
		 * Get Menu parent for top-level menu.
		 *
		 * @since 1.0.5
		 */
		public static function get_menu_parent() {

			$parent            = ! ( apm_get_option( 'apmautic_menu_position' ) ) ? 'options-general.php' : apm_get_option( 'apmautic_menu_position' );
			$is_top_level_page = in_array( $parent, array( 'top', 'middle', 'bottom' ), true );
			if ( $is_top_level_page ) {
				$parent = 'admin.php';
			}
			return $parent;
		}

		/**
		 * Checks to see if a settings form is supported.
		 *
		 * @since 1.0.0
		 * @param string $type The type of form to check.
		 * @return bool
		 */
		public static function has_support( $type ) {
			return file_exists( AP_MAUTIC_PLUGIN_DIR . 'includes/admin-settings-' . $type . '.php' );
		}

		/**
		 * Saves the admin settings.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function save() {
			// Only admins can save settings.
			if ( ! current_user_can( 'delete_users' ) ) {
				return;
			}

			if ( isset( $_POST['apm-post-meta-nonce'] ) && wp_verify_nonce( $_POST['apm-post-meta-nonce'], 'apmauticpmeta' ) ) {
				$rule_id           = '';
				$update_conditions = '';
				if ( isset( $_POST['ampw_rule_title'] ) ) {
					$rule_name = esc_attr( $_POST['ampw_rule_title'] );
				}

				// Gather post data.
				$rule_post_type = array(
					'post_title'   => $rule_name,
					'post_content' => '',
					'post_status'  => 'publish',
					'post_type'    => AP_MAUTIC_POSTTYPE,
				);

				if ( isset( $_GET['action'] ) && 'edit' == $_GET['action'] ) {
					$rule_id = esc_attr( $_GET['post'] );
				}

				if ( ! empty( $rule_id ) && null != $rule_id ) {
					$rule_post_type['ID'] = $rule_id;
				}

				$rule_id = wp_insert_post( $rule_post_type );
				$post_id = $rule_id;

				// update post meta.
				if ( isset( $_POST['pm_condition'] ) ) {
					$conditions = $_POST['pm_condition'];
					$cp_keys    = array_keys( $conditions, 'CP' );

					$condition_cnt = sizeof( $conditions );

					for ( $i = 0; $i < $condition_cnt; $i++ ) {
						if ( 'UR' == $conditions[ $i ] ) {
							$base              = sanitize_text_field( $conditions[ $i ] );
							$update_conditions = array( $i => array( $base ) );
						}
						if ( 'CP' == $conditions[ $i ] ) {
							$sub_key           = array_search( $i, $cp_keys );
							$ss_cp_condition   = isset( $_POST['ss_cp_condition'][ $sub_key ] ) ? sanitize_text_field( $_POST['ss_cp_condition'][ $sub_key ] ) : '';
							$base              = sanitize_text_field( $conditions[ $i ] );
							$sub_cp_condition  = sanitize_text_field( $_POST['sub_cp_condition'][ $sub_key ] );
							$update_conditions = array(
								$i => array(
									$base,
									$sub_cp_condition,
									$ss_cp_condition,
								),
							);
						}
						if ( 'CP_APPROVE' == $conditions[ $i ] ) {
							$sub_key           = array_search( $i, $cp_keys );
							$ss_cp_condition   = isset( $_POST['ss_cp_condition'][ $sub_key ] ) ? sanitize_text_field( $_POST['ss_cp_condition'][ $sub_key ] ) : '';
							$base              = sanitize_text_field( $conditions[ $i ] );
							$sub_cp_condition  = sanitize_text_field( $_POST['sub_cp_condition'][ $sub_key ] );
							$update_conditions = array(
								$i => array(
									$base,
									$sub_cp_condition,
									$ss_cp_condition,
								),
							);
						}
						$action            = 'update_condition_' . $conditions[ $i ];
						$update_conditions = apply_filters( $action, $update_conditions, $conditions, $i, $_POST );
					}

					$update_conditions = serialize( $update_conditions );
					update_post_meta( $post_id, 'ampw_rule_condition', $update_conditions );
				}
					// update actions.
				if ( isset( $_POST['sub_seg_action'] ) ) {

					$actions = $_POST['sub_seg_action'];

					$action_cnt = sizeof( $actions );

					for ( $i = 0; $i < $action_cnt; $i++ ) {

						$sub_seg_action       = sanitize_text_field( $_POST['sub_seg_action'][ $i ] );
						$ss_seg_action        = sanitize_text_field( $_POST['ss_seg_action'][ $i ] );
						$update_actions[ $i ] = array(
							$sub_seg_action,
							$ss_seg_action,
						);
						$action               = 'update_action_' . $actions[ $i ];
						$update_action        = apply_filters( $action, $update_actions, $actions, $i, $_POST );
					}

					$update_actions = serialize( $update_actions );
					update_post_meta( $post_id, 'ampw_rule_action', $update_actions );
				}
					$redirect = APMautic_AdminSettings::get_render_page_url( "&action=edit&post=$post_id" );
					wp_redirect( $redirect );
					exit();
			}

			if ( isset( $_POST['ap-mautic-nonce'] ) && wp_verify_nonce( $_POST['ap-mautic-nonce'], 'apmwmautic' ) ) {

				$amp_options = APMautic_Helper::get_amp_options();

				$amp_options['base-url'] = isset( $_POST['base-url'] ) ? esc_url( $_POST['base-url'] ) : '';

				$amp_options['public-key'] = isset( $_POST['public-key'] ) ? esc_attr( $_POST['public-key'] ) : '';

				$amp_options['secret-key'] = isset( $_POST['secret-key'] ) ? esc_attr( $_POST['secret-key'] ) : '';

				$amp_options['callback-uri'] = isset( $_POST['callback-uri'] ) ? esc_attr( $_POST['callback-uri'] ) : '';

				$mautic_api_url          = $amp_options['base-url'];
				$amp_options['base-url'] = rtrim( $mautic_api_url, '/' );

				update_option( AP_MAUTIC_PLUGIN_CONFIG, $amp_options );
			}
			if ( isset( $_POST['ap-mautic-nonce-tracking'] ) && wp_verify_nonce( $_POST['ap-mautic-nonce-tracking'], 'apmautictrack' ) ) {

				$amp_options = APMautic_Helper::get_amp_options();

				$amp_options['enable-tracking'] = false;

				if ( isset( $_POST['enable-tracking'] ) ) {

					$amp_options['enable-tracking'] = true;
				}

				update_option( AP_MAUTIC_PLUGIN_CONFIG, $amp_options );

				$redirect = APMautic_AdminSettings::get_render_page_url( '&tab=enable_tracking' );
				wp_redirect( $redirect );
				exit();
			}

			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'delete-rule' . $_GET['rule_id'] ) ) {
				if ( isset( $_GET['rule_id'] ) ) {
					$rule_id = esc_attr( $_GET['rule_id'] );
					wp_delete_post( $rule_id );
					$redirect = APMautic_AdminSettings::get_render_page_url( '&tab=all_rules' );
					wp_redirect( $redirect );
					exit();
				}
			}

			do_action( 'amp_update_tab_content' );
		}

		/**
		 * Call authenticate update.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function mautic_authenticate_update() {

			if ( isset( $_POST['ampw-save-authenticate'] ) && 'Save and Authenticate' == esc_attr( $_POST['ampw-save-authenticate'] ) ) {
				$post_data = $_POST;
				$instance  = APMautic_Services::get_service_instance( AP_MAUTIC_SERVICE );
				$instance->connect( $post_data );
			}
		}

		/**
		 * Call authenticate update.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function ap_mautic_notices() {
			$curr_screen = isset( $_REQUEST['page'] ) ? esc_attr( $_REQUEST['page'] ) : '';
			if ( ! APMautic_Services::is_connected() && AP_MAUTIC_POSTTYPE == $curr_screen ) {

				$redirect = APMautic_AdminSettings::get_render_page_url( '&tab=auth_mautic' );
				// translators: %s: redirect url.
				printf( __( '<div class="update-nag"> Seems there appears error with the Mautic configuration. <i><a href="%s">click here</a></i> to authenticate Mautic.</div>', 'automate-mautic' ), $redirect );
			}
		}

		/**
		 * Render Message
		 *
		 * @since 1.0.0
		 * @param string $message message text.
		 * @return void
		 */
		public static function render_messages( $message ) {
			$curr_screen = isset( $_REQUEST['page'] ) ? esc_attr( $_REQUEST['page'] ) : '';
			if ( 'update' == $message && AP_MAUTIC_POSTTYPE == $curr_screen ) {
				echo '<div class="updated"><p>' . __( 'Settings updated!', 'automate-mautic' ) . '</p></div>';
			}
		}

		/**
		 * Render tab items
		 *
		 * @since 1.1.0
		 * @param array  $items tab items.
		 * @param string $active active tab.
		 * @return void
		 */
		public static function render_tab_items( $items, $active ) {
			$output = '';
			foreach ( $items as $slug => $data ) {
				$page_slug  = '&tab=' . $slug;
				$active_tab = ( $slug == $active ) ? 'nav-tab-active' : '';
				$url        = APMautic_AdminSettings::get_render_page_url( $page_slug );
				$output    .= "<a class='nav-tab " . esc_attr( $active_tab ) . "' href='" . esc_url( $url ) . "'>" . esc_attr( $data['label'] ) . '</a>';
			}
			echo $output;
		}

		/**
		 * Add automate access capabilities to user roles
		 *
		 * @since 1.0.0
		 */
		public static function access_capabilities() {

			if ( is_user_logged_in() ) {
				if ( current_user_can( 'manage_options' ) ) {

					global $wp_roles;
					$wp_roles_data = $wp_roles->get_names();
					$roles         = false;

					$roles = apm_get_option( 'apmautic_access_role' );

					if ( ! $roles ) {
						$roles = array();
					}

					// give access to administrator.
					$roles[] = 'administrator';

					foreach ( $wp_roles_data as $key => $value ) {
						$role = get_role( $key );

						if ( in_array( $key, $roles ) ) {
							$role->add_cap( 'access_automate_mautic' );
						} else {
							$role->remove_cap( 'access_automate_mautic' );
						}
					}
				}
			}
		}
	}
	APMautic_AdminSettings::instance();
endif;
