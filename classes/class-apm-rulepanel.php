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
			require_once AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'classes/class-apm-admin-ajax.php';
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
		public static function apmw_metabox_view() {
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

			$all_pages = '<select id="sub-sub-condition" class="root-cp-condition form-control" name="ss_cp_condition[]">';
			$pages = get_pages();
			$all_pages .= '<option>' . __( 'Select Page', 'automateplus-mautic-wp' ) . '</option>';

			foreach ( $pages as $page ) {
				$all_pages .= self::make_option( $page->ID, $page->post_title, $select );
			}
			$all_pages .= '</select>';
			echo $all_pages;
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
			$all_posts = '<select id="ss-cp-condition" class="root-cp-condition form-control" name="ss_cp_condition[]">';
			$args = array( 'posts_per_page' => -1 );
			$posts = get_posts( $args );
			$all_posts .= '<option>' . __( 'Select Post', 'automateplus-mautic-wp' ) . '</option>';
			foreach ( $posts as $post ) : setup_postdata( $post );
				$all_posts .= self::make_option( $post->ID, $post->post_title, $select );
				endforeach;
			$all_posts .= '</select>';
			wp_reset_postdata();
			echo $all_posts;
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
				$method = 'GET';
				$body['limit'] = 100000;

				$segments = APMautic_API::ampw_mautic_api_call( $url, $method, $body );

				if ( ! APMautic_API::is_connected() || isset( $segments->errors ) ) {
					return;
				}
				set_transient( 'apm_all_segments', $segments , DAY_IN_SECONDS );
			}

			if ( empty( $segments ) || ! APMautic_API::is_connected() ) {
				echo __( 'THERE APPEARS TO BE AN ERROR WITH THE CONFIGURATION.', 'automateplus-mautic-wp' );
				return;
			}

			$all_segments = '<select class="root-seg-action" name="ss_seg_action[]">';
			$all_segments .= '<option>' . __( 'Select Segment', 'automateplus-mautic-wp' ) . '</option>';

			foreach ( $segments->lists as $offset => $list ) {
				$all_segments .= self::make_option( $list->id, $list->name, $select );
			}
			$all_segments .= '</select>';
			echo $all_segments;
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
			if ( AUTOMATEPLUS_MAUTIC_POSTTYPE != $post_type ) {
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
			$args = array( 'posts_per_page' => -1, 'post_status' => 'publish', 'post_type' => AUTOMATEPLUS_MAUTIC_POSTTYPE );
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
			$args = array( 'posts_per_page' => -1, 'post_status' => 'publish', 'post_type' => AUTOMATEPLUS_MAUTIC_POSTTYPE );
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
			$conditions = '<option>' . __( 'Select Condition', 'automateplus-mautic-wp' ) . '</option>
			<option value="UR" ' . selected( $select, 'UR' ) . '>' . __( 'User Register on WordPress', 'automateplus-mautic-wp' ) . '</option>
			<option value="CP" ' . selected( $select, 'CP' ) . '>' . __( 'User Post a Comment', 'automateplus-mautic-wp' ) . '</option>';

			$all_conditions = apply_filters( 'amp_mautic_conditions_list', $conditions, $select );
			echo $all_conditions;
		}

		/**
		 * Get all actons list
		 *
		 * @param array $select selected value.
		 * @return void
		 */
		public static function get_all_actions_list( $select = '' ) {
			$actions = '<option value="add_segment" ' . selected( $select, 'add_segment' ) . '>' . __( 'Add to segment', 'automateplus-mautic-wp' ) . '</option>
			<option value="remove_segment" ' . selected( $select, 'remove_segment' ) . '>' . __( 'Remove from segment', 'automateplus-mautic-wp' ) . '</option>
			<option value="add_tag" ' . selected( $select, 'add_tag' ) . '>' . __( 'Add Tags', 'automateplus-mautic-wp' ) . '</option>';
			$all_actions = apply_filters( 'amp_mautic_actions_list', $actions );
			echo $all_actions;
		}

		/**
		 * Get comment subcondition list
		 *
		 * @param array $select selected value.
		 * @return void
		 */
		public static function get_comment_condition_sublist( $select = '' ) {
			$comment_sublist = '<option value="ao_website" ' . selected( $select, 'ao_website' ) . '>' . __( 'Anywhere On Website', 'automateplus-mautic-wp' ) . '</option>
			<option value="os_page" ' . selected( $select, 'os_page' ) . '>' . __( 'On Specific Page', 'automateplus-mautic-wp' ) . '</option>
			<option value="os_post" ' . selected( $select, 'os_post' ) . '>' . __( 'On Specific Post', 'automateplus-mautic-wp' ) . '</option>';
			$comment_sublist = apply_filters( 'amp_mautic_comment_condition_sublist', $comment_sublist );
			echo $comment_sublist;
		}
	}
	APMautic_RulePanel::instance();
endif;
