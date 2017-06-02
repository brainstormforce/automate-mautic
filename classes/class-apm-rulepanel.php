<?php
/**
 * Rules Post Meta
 *
 * @package automateplus-mautic
 * @since 1.0.0
 */

if ( ! class_exists( 'APMautic_RulePanel' ) ) :

	/**
	 * Create class APMautic_RulePanel
	 * Generate data for rule panel
	 */
	class APMautic_RulePanel {

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
				self::$instance = new APMautic_RulePanel();
				self::$instance->hooks();
				self::$instance->includes();
			}
			return self::$instance;
		}

		/**
		 * Include files
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function includes() {
			require_once AP_MAUTIC_PLUGIN_DIR . 'classes/class-apm-admin-ajax.php';
		}

		/**
		 * Call hooks
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function hooks() {
			add_action( 'wp_trash_post', array( $this, 'clean_condition_actions' ) );
		}

		/**
		 * Include files
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function ap_mautic_metabox_view() {
			APMautic_AdminSettings::render_form( 'post-meta' );
		}

		/**
		 * Make options html
		 *
		 * @param int/string $id value.
		 * @param string     $value option text.
		 * @param string     $selected selected value.
		 * @since 1.0.0
		 * @return string
		 */
		public static function make_option( $id, $value, $selected = null ) {
			$selected = selected( $id, $selected, false );
			return '<option value="' . $id . '"' . $selected . '>' . $value . '</option>';
		}

		/**
		 * Get all pages list
		 *
		 * @param string $select selected value.
		 * @since 1.0.0
		 * @return void
		 */
		public static function select_all_pages( $select = null ) {

			$pages = get_pages();
			$options = array( '' => __( 'Select Page', 'automateplus-mautic-wp' ) );
			foreach ( $pages as $page ) {
				$options[ $page->ID ] = $page->post_title;
			}

			APMautic_helper::render_settings_field( 'ss_cp_condition[]', array(
				'type'			=> 'select',
				'id'			=> 'sub-sub-condition',
				'class'			=> 'root-cp-condition form-control',
				'options'		=> $options,
				'selected'		=> $select
			));
		}

		/**
		 * Get all posts list
		 *
		 * @param string $select selected value.
		 * @since 1.0.0
		 * @return void
		 */
		public static function select_all_posts( $select = null ) {
			// get all posts.
			$args = array( 'posts_per_page' => -1 );
			$posts = get_posts( $args );
			$options = array( '' => __( 'Select Post', 'automateplus-mautic-wp' ) );
			foreach ( $posts as $post ) : setup_postdata( $post );
				$options[ $post->ID ] = $post->post_title;
			endforeach;

			APMautic_helper::render_settings_field( 'ss_cp_condition[]', array(
				'type'			=> 'select',
				'id'			=> 'ss-cp-condition',
				'class'			=> 'root-cp-condition form-control',
				'options'		=> $options,
				'selected'		=> $select
			));
			wp_reset_postdata();
		}

		/**
		 * Get all segments list
		 *
		 * @param string $select selected value.
		 * @since 1.0.0
		 * @return void
		 */
		public static function select_all_segments( $select = null ) {
			// get all segments.
			$segments = '';
			$segments_trans = get_transient( 'apm_all_segments' );

			if ( $segments_trans ) {
				$segments = $segments_trans;
			} else {
				$url = '/api/segments/';
				$body['limit'] = 100000;

				$segments = APMauticServices::mautic_api_get_data( $url, $body );

				if ( ! APMauticServices::is_connected() || isset( $segments->errors ) ) {
					return;
				}
				set_transient( 'apm_all_segments', $segments , DAY_IN_SECONDS );
			}

			if ( empty( $segments ) || ! APMauticServices::is_connected() ) {
				echo __( 'THERE APPEARS TO BE AN ERROR WITH THE CONFIGURATION.', 'automateplus-mautic-wp' );
				return;
			}

			$instance   = APMauticServices::get_service_instance( AP_MAUTIC_SERVICE );
			$instance->render_list_field( $segments->lists, $select );
		}

		/**
		 * Delete meta assosites with rule
		 *
		 * @param int $post_id rule ID.
		 * @since 1.0.0
		 * @return void
		 */
		public static function clean_condition_actions( $post_id ) {
			$post_type = get_post_type( $post_id );
			if ( AP_MAUTIC_POSTTYPE != $post_type ) {
				return;
			}
			delete_post_meta( $post_id, 'ampw_rule_condition' );
			delete_post_meta( $post_id, 'ampw_rule_action' );
		}

		/**
		 * Check if rule is set
		 *
		 * @param array $comment_data comment data.
		 * @return rule id array
		 */
		public static function get_comment_condition( $comment_data = array() ) {
			$args = array( 'posts_per_page' => -1, 'post_status' => 'publish', 'post_type' => AP_MAUTIC_POSTTYPE );
			$posts = get_posts( $args );
			$set_rules = array();
			foreach ( $posts as $post ) : setup_postdata( $post );
				$rule_id = $post->ID;
				$meta_conditions = get_post_meta( $rule_id, 'ampw_rule_condition' );
				$meta_conditions = unserialize( $meta_conditions[0] );
				foreach ( $meta_conditions as $order => $meta_condition ) :
					if ( 'CP' == $meta_condition[0] ) {
						if ( 'ao_website' == $meta_condition[1] ) {

							// add rule_id into array.
							array_push( $set_rules, $rule_id );
						}
						if ( 'os_page' == $meta_condition[1] || 'os_post' == $meta_condition[1]  ) {
							if ( is_array( $comment_data ) && $meta_condition[2] == $comment_data['comment_post_ID'] ) {
								array_push( $set_rules, $rule_id );
							}
						}
					}
					endforeach;
				endforeach;
			return $set_rules;
		}

		/**
		 * Get rules where user register condition is set
		 *
		 * @return array
		 */
		public static function get_wpur_condition() {
			$args = array( 'posts_per_page' => -1, 'post_status' => 'publish', 'post_type' => AP_MAUTIC_POSTTYPE );
			$posts = get_posts( $args );
			$ur_rules = array();
			foreach ( $posts as $post ) : setup_postdata( $post );
				$rule_id = $post->ID;
				$meta_conditions = get_post_meta( $rule_id, 'ampw_rule_condition' );
				$all_conditions = unserialize( $meta_conditions[0] );
				foreach ( $all_conditions as $meta_condition ) :
					if ( 'UR' == $meta_condition[0] ) {
						// add rule_id into array.
						array_push( $ur_rules, $rule_id );
					}
					endforeach;
				endforeach;
			return $ur_rules;
		}

		/**
		 * Get all rules and manipulate actions
		 *
		 * @param array $rules rule ID's.
		 * @return array all actions
		 */
		public static function get_all_actions( $rules = array() ) {

			$all_actions = array(
				'add_segment' => array(),
				'remove_segment' => array(),
				'add_tag' => array(),
			);

			foreach ( $rules as $rule ) :
				$rule_id = $rule;
				$meta_actions = get_post_meta( $rule_id, 'ampw_rule_action' );
				$meta_actions = unserialize( $meta_actions[0] );

				foreach ( $meta_actions as $order => $meta_action ) :
					if ( 'add_segment' == $meta_action[0] ) {

						$segment_id = $meta_action[1];
						array_push( $all_actions['add_segment'], $segment_id );
					}
					if ( 'remove_segment' == $meta_action[0] ) {

						$segment_id = $meta_action[1];
						array_push( $all_actions['remove_segment'], $segment_id );
					}
					if ( 'add_tag' == $meta_action[0] ) {
						array_push( $all_actions['add_tag'], $meta_action[1] );
					}

					endforeach;
				endforeach;

			return $all_actions;
		}

		/**
		 * Get all conditions list
		 *
		 * @param array $select selected value.
		 * @return void
		 */
		public static function get_all_conditions_list( $select = '' ) {

			$options = array(
				''		=>	__( 'Select Condition', 'automateplus-mautic-wp' ),
				'UR'	=>	__( 'User Register on WordPress', 'automateplus-mautic-wp' ),
				'CP'	=>	__( 'User Post a Comment', 'automateplus-mautic-wp' )
			);

			$options = apply_filters( 'amp_mautic_conditions_list', $options );

			APMautic_helper::render_settings_field( 'pm_condition[]', array(
				'type'			=> 'select',
				'id'			=> 'selct-condition-list',
				'class'			=> 'select-condition form-control',
				'options'		=> $options,
				'selected'		=> $select
			));
		}

		/**
		 * Get all actons list
		 *
		 * @param array $select selected value.
		 * @return void
		 */
		public static function get_all_actions_list( $select = '' ) {

			$options = array(
				'add_segment'		=>	__( 'Add to segment', 'automateplus-mautic-wp' ),
				'remove_segment'	=>	__( 'Remove from segment', 'automateplus-mautic-wp' ),
				'add_tag'			=>	__( 'Add Tags', 'automateplus-mautic-wp' )
			);

			$options = apply_filters( 'amp_mautic_actions_list', $options );

			APMautic_helper::render_settings_field( 'sub_seg_action[]', array(
				'type'			=> 'select',
				'id'			=> 'sub-seg-action',
				'class'			=> 'sub-seg-action form-control',
				'options'		=> $options,
				'selected'		=> $select
			));

		}

		/**
		 * Get comment subcondition list
		 *
		 * @param array $select selected value.
		 * @return void
		 */
		public static function get_comment_condition_sublist( $select = '' ) {

			$options = array(
				'ao_website'	=>	__( 'Anywhere On Website', 'automateplus-mautic-wp' ),
				'os_page'		=>	__( 'On Specific Page', 'automateplus-mautic-wp' ),
				'os_post'		=>	__( 'On Specific Post', 'automateplus-mautic-wp' )
			);

			$options = apply_filters( 'amp_mautic_comment_condition_sublist', $options );

			APMautic_helper::render_settings_field( 'sub_cp_condition[]', array(
				'type'			=> 'select',
				'id'			=> 'sub-cp-condition',
				'class'			=> 'sub-cp-condition form-control',
				'options'		=> $options,
				'selected'		=> $select
			));
		}
	}
	APMautic_RulePanel::instance();
endif;
