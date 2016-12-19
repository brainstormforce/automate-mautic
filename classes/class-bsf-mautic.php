<?php
/**
 * BSF Mautic initial setup
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'BSF_Mautic' ) ) :

	class BSF_Mautic {

		private static $instance;
		/**
		 * Initiator
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new BSF_Mautic();
				self::$instance->includes();
				self::$instance->hooks();
			}
			return self::$instance;
		}
		public function includes() {
			require_once BSF_MAUTIC_PLUGIN_DIR . '/classes/class-bsfm-init.php';
			require_once BSF_MAUTIC_PLUGIN_DIR . '/classes/class-bsfm-postmeta.php';
		}
		public function hooks() {
			add_action( 'init', array( $this, 'bsf_mautic_register_posttype' ) );
			add_action( 'wp_head', array( $this, 'bsf_mautic_tracking_script' ) );
			add_action( 'wp_footer', array( $this, 'bsf_mautic_tracking_image' ) );
			add_action( 'user_register', array( $this, 'bsfm_add_registered_user' ), 10, 1 );
			add_action( 'comment_post', array( $this, 'bsfm_add_comment_author' ), 10, 3 );
			add_filter( 'wpcf7_before_send_mail', array( $this, 'bsfm_filter_cf7_submit_fields' ) );
			add_action( 'edd_update_payment_status', array( $this, 'bsfm_edd_purchase_to_mautic' ), 10, 3 );
			add_action( 'edd_update_payment_status', array( $this, 'bsfm_edd_to_mautic_config' ), 10, 3 );

			// add refresh links to footer
			// add_action( 'admin_init', array( $this, 'wpse_edit_footer' ));

			add_action( 'edd_purchase_form_user_info_fields', array( $this, 'mautic_edd_display_checkout_fields' ) );
			// add_action( 'wp_footer', array( $this, 'mautic_edd_display_checkout_fields' ) );
		}

		// public static function wpse_edit_footer() {

		//     add_filter( 'admin_footer_text', array( $this, 'wpse_edit_text' ), 11 );
		// }

		// public static function wpse_edit_text($content) {
		//     return "";
		// }

		public function mautic_edd_display_checkout_fields() {
			$adminajax =  admin_url( 'admin-ajax.php' );

			wp_enqueue_script( 'bsfm-proactive-ab' , BSF_MAUTIC_PLUGIN_URL . 'assets/js/bsfm-proactive-ab.js', __FILE__ , array(), '1.0.0', false );
			$bsfm_select_params = array(
				'bsf_ajax_url'	=> $adminajax
		);

			wp_localize_script( 'bsfm-proactive-ab', 'bsf_widget_notices', $bsfm_select_params );
 
			?>
			<script>
					
					jQuery(window).on( 'load', function(){
					var temp = jQuery('#edd-email').val();
						console.log(temp);
						var data= {
							action:'get_edd_var_price',
							///email: temp,
							// ajaxurl: 
						};

						jQuery.post(ajaxurl, data, function(selHtml) {
								console.log(selHtml);
						});

				});
			</script>
			<?php
		}
		/**
		 * Register a bsf-mautic-rule post type.
		 * @since 1.0.0
		 * @link http://codex.wordpress.org/Function_Reference/register_post_type
		 */
		public function bsf_mautic_register_posttype() {
			$labels = array(
				'name'               => _x( 'Rules', 'post type general name', 'bsfmautic' ),
				'singular_name'      => _x( 'Rule', 'post type singular name', 'bsfmautic' ),
				'menu_name'          => _x( 'Rules', 'admin menu', 'bsfmautic' ),
				'name_admin_bar'     => _x( 'Rule', 'add new on admin bar', 'bsfmautic' ),
				'add_new'            => _x( 'Add New', 'rule', 'bsfmautic' ),
				'add_new_item'       => __( 'Add New Rule', 'bsfmautic' ),
				'new_item'           => __( 'New Rule', 'bsfmautic' ),
				'edit_item'          => __( 'Edit Rule', 'bsfmautic' ),
				'view_item'          => __( 'View Rule', 'bsfmautic' ),
				'all_items'          => __( 'All Rules', 'bsfmautic' ),
				'search_items'       => __( 'Search Rules', 'bsfmautic' ),
				'parent_item_colon'  => __( 'Parent Rules:', 'bsfmautic' ),
				'not_found'          => __( 'No rules found.', 'bsfmautic' ),
				'not_found_in_trash' => __( 'No rules found in Trash.', 'bsfmautic' )
			);
			$args = array(
				'labels'             => $labels,
				'description'        => __( 'Description.', 'bsfmautic' ),
				'public'             => false,
				'publicly_queryable' => false,
				'show_ui'            => false,
				'show_in_menu'       => 'options-general.php',
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'bsf-mautic-rule' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'menu_icon'			 => 'dashicons-chart-line',
				'supports'           => array( 'title' )
			);
			register_post_type( 'bsf-mautic-rule', $args );
		}

		/** 
		 * Writes Mautic Tracking JS to the HTML source of WP head
		 *
		 * @since 1.0.0
		 */
		public function bsf_mautic_tracking_script()
		{
			$bsfm_options = BSF_Mautic_Init::$bsfm_options['bsf_mautic_settings'];
			$enable_mautic_tracking	= false;
			if ( !empty( $bsfm_options ) && array_key_exists( 'bsfm-enabled-tracking', $bsfm_options ) ) {
				if( $bsfm_options['bsfm-enabled-tracking'] == 1 && $bsfm_options['bsfm-tracking-type'] == 'js' ) {
					$enable_mautic_tracking = true;
				} else {
					$enable_mautic_tracking = false;
				}
			}
			if ( $enable_mautic_tracking ) {
				$base_url = trim($bsfm_options['bsfm-base-url'], " \t\n\r\0\x0B/");
				$bsfm_trackingJS = "<script>
				(function(w,d,t,u,n,a,m){w['MauticTrackingObject']=n;
				w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),
				m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)
				})(window,document,'script','{$base_url}/mtc.js','mt');
				mt('send', 'pageview');
				</script>";
				echo $bsfm_trackingJS;
			}
		}

		/** 
		 * Writes Mautic Tracking image to site 
		 *
		 * @since 1.0.0
		 */
		public function bsf_mautic_tracking_image( $atts, $content = null )
		{
			global $wp;
			$bsfm_options = BSF_Mautic_Init::$bsfm_options['bsf_mautic_settings'];
			$enable_img_tracking = false;
			if ( !empty( $bsfm_options ) && array_key_exists( 'bsfm-enabled-tracking', $bsfm_options ) ) { 
				if( $bsfm_options['bsfm-enabled-tracking'] == 1 && $bsfm_options['bsfm-tracking-type'] == 'img' ) {
					$enable_img_tracking = true;
				} else {
					$enable_img_tracking = false;
				}
			}
			if ( $enable_img_tracking ) {
				$base_url = trim($bsfm_options['bsfm-base-url'], " \t\n\r\0\x0B/");
				$current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
				$attrs = array();
				$attrs['title']	 = 'title';
				$attrs['language']  = get_locale();
				$attrs['referrer']  = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $current_url;
				$attrs['url']	   = $current_url;
				$url_query = $attrs;
				$encoded_query = urlencode(base64_encode(serialize($url_query)));
				$image = '<img style="display:none" src="' . $base_url . '/mtracking.gif?d=' . $encoded_query . '" alt="mautic is open source marketing automation" />';
				echo $image;
			}
		}
		
		/** 
		 * Add registered WP users to Mautic contacts
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function bsfm_add_registered_user( $user_id ) {
			if( !$user_id ) return;
			//get user registerd condition rules
			$status = Bsfm_Postmeta::bsfm_get_wpur_condition();
			if( is_array($status) && sizeof($status)>0 ) {
				$set_actions = Bsfm_Postmeta::bsfm_get_all_actions($status);
			}
			else {
				return;
			}

			$user_info = get_userdata( $user_id );
			$email = $user_info->user_email;
			$credentials = get_option( 'bsfm_mautic_credentials' );

			if( isset($_COOKIE['mtc_id']) ) {
				$contact_id = $_COOKIE['mtc_id'];
				$contact_id = (int)$contact_id;
			}
			else {
				$contact_id = self::bsfm_mautic_get_contact_by_email( $email, $credentials );
			}
			
			if( isset($contact_id) ) {
				$method = 'PATCH';
				$url = '/api/contacts/'.$contact_id.'/edit';
				//add to segment
				$add_segment = $set_actions['add_segment'];
				if( is_array( $add_segment ) ) {
					foreach ( $add_segment as $segment_id) {
						$segment_id = (int)$segment_id;
						$action = "add";
						$res = self::bsfm_mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action);
					}
				}
			}
			else {
				$method = 'POST';
				$url = '/api/contacts/new';
			}

			$body = array(
				'firstname'	=> $user_info->first_name,
				'lastname'	=> $user_info->last_name,
				'email'		=> $user_info->user_email,
				'website'	=> $user_info->user_url
			);
			// 'tags'		=> ''
			// API Method
			$remove_segment = $set_actions['remove_segment'];
			if( is_array( $remove_segment ) && ( sizeof($remove_segment)>0 ) ) {
				self::bsfm_remove_contact_from_segment( $body, $set_actions );
			}
			$add_segment = $set_actions['add_segment'];
			if( is_array( $add_segment ) && ( sizeof( $add_segment )>0 ) ) {
				self::bsfm_mautic_api_call($url, $method, $body, $set_actions);
			}
		}

		/** 
		 * Add comments author to Mautic contacts
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function bsfm_add_comment_author( $id, $approved, $commentdata ) {
			if( !isset($commentdata['comment_author_email']) ) return;
			//get comment post condition rules
			$status = Bsfm_Postmeta::bsfm_get_comment_condition( $commentdata );
			if( is_array($status) && sizeof($status)>0 ) {
				$set_actions = Bsfm_Postmeta::bsfm_get_all_actions($status);
			}
			else {
				return;
			}
			$email = $commentdata['comment_author_email'];
			$credentials = get_option( 'bsfm_mautic_credentials' );
			if( isset($_COOKIE['mtc_id']) ) {
				$contact_id = $_COOKIE['mtc_id'];
				$contact_id = (int)$contact_id;
			}
			else {
				$contact_id = self::bsfm_mautic_get_contact_by_email( $email, $credentials );
			}

			if( isset( $contact_id ) ) {
				$method = 'PATCH';
				$url = '/api/contacts/'.$contact_id.'/edit';
				//add to segment
				$add_segment = $set_actions['add_segment'];
				if( is_array( $add_segment ) ) {
					foreach ( $add_segment as $segment_id) {
						$segment_id = (int)$segment_id;
						$action = "add";
						$res = self::bsfm_mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action);
					}
				}
			}
			else {
				$method = 'POST';
				$url = '/api/contacts/new';
			}
			$body = array(
				'firstname'	=>	$commentdata['comment_author'],
				'email'		=>	$commentdata['comment_author_email'],
				'website'	=>	$commentdata['comment_author_url']
			);
		 	$remove_segment = $set_actions['remove_segment'];
			if( is_array( $remove_segment ) && ( sizeof($remove_segment)>0 ) ) {
				self::bsfm_remove_contact_from_segment( $body, $set_actions );
			}

			$add_segment = $set_actions['add_segment'];

			if( is_array( $add_segment ) && ( sizeof( $add_segment )>0 ) ) {
				self::bsfm_mautic_api_call($url, $method, $body, $set_actions);
			}
		}

		/** 
		 * Add edd purchasers to Mautic
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function bsfm_edd_purchase_to_mautic( $payment_id, $new_status, $old_status ) {
			// Basic payment meta			
			$payment_meta = edd_get_payment_meta( $payment_id );

			$email = $payment_meta['user_info']['email'];
			$credentials = get_option( 'bsfm_mautic_credentials' );

			$status = Bsfm_Postmeta::bsfm_get_edd_condition( $payment_meta, $new_status );
			if( is_array($status) && sizeof($status)>0 ) {
				$set_actions = Bsfm_Postmeta::bsfm_get_all_actions($status);
			}
			else {
				return;
			}

			if( isset($_COOKIE['mtc_id']) ) {
				$contact_id = $_COOKIE['mtc_id'];
				$contact_id = (int)$contact_id;
			}
			else {
				$contact_id = self::bsfm_mautic_get_contact_by_email( $email, $credentials );
			}

			if( isset( $contact_id ) ) {
				$method = 'PATCH';
				$url = '/api/contacts/'.$contact_id.'/edit';
				//add to segment
				$add_segment = $set_actions['add_segment'];
				if( is_array( $add_segment ) ) {
					foreach ( $add_segment as $segment_id) {
						$segment_id = (int)$segment_id;
						$action = "add";
						$res = self::bsfm_mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action);
					}
				}
			}
			else {
				$method = 'POST';
				$url = '/api/contacts/new';
			}

			$body = array(
				'firstname'	=>	$payment_meta['user_info']['first_name'],
				'lastname'	=>	$payment_meta['user_info']['last_name'],
				'email'		=>	$payment_meta['user_info']['email']
			);

			$remove_segment = $set_actions['remove_segment'];
			if( is_array( $remove_segment ) && ( sizeof( $remove_segment )>0 ) ) {
				self::bsfm_remove_contact_from_segment( $body, $set_actions );
			}
			$add_segment = $set_actions['add_segment'];
			if( is_array( $add_segment ) && ( sizeof( $add_segment )>0 ) ) {
				self::bsfm_mautic_api_call($url, $method, $body, $set_actions);
			}
		}
		/** 
		 * Add edd purchasers to Mautic set in config
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function bsfm_edd_to_mautic_config( $payment_id, $new_status, $old_status ) {

			if( $new_status == 'publish' || $new_status == 'abandoned' ) {
				// Basic payment meta			
				$payment_meta = edd_get_payment_meta( $payment_id );

				// get all downloads
				$all_downloads = $payment_meta['downloads'];
				$all_products = array();
				foreach ( $all_downloads as $download ) {
			 		array_push( $all_products, $download['id'] );
				}
				//$query = new WP_Query( array( 'post_status' => 'publish', 'post_type' => 'download', 'post__in' => $all_products ) );
				$set_rules = $download_id = $price_id = $m_tags = array();
				$bsfm_opt = get_option('_bsf_mautic_config');
				$bsfm_edd_prod_slug	= array_key_exists( 'bsfm_edd_prod_slug', $bsfm_opt ) ? $bsfm_opt['bsfm_edd_prod_slug'] : '';
				$bsfm_edd_prod_cat = array_key_exists( 'bsfm_edd_prod_cat', $bsfm_opt ) ? $bsfm_opt['bsfm_edd_prod_cat'] : '';
				$bsfm_edd_prod_tag	= array_key_exists( 'bsfm_edd_prod_tag', $bsfm_opt ) ? $bsfm_opt['bsfm_edd_prod_tag'] : '';
				$seg_action_id = array_key_exists( 'config_edd_segment', $bsfm_opt ) ? $bsfm_opt['config_edd_segment'] : '';
				$seg_action_ab = array_key_exists( 'config_edd_segment_ab', $bsfm_opt ) ? $bsfm_opt['config_edd_segment_ab'] : '';

				$args = array( 'post_type'	=>	'download', 'posts_per_page' => -1, 'post_status' => 'publish', 'post__in' => $all_products );
				$downloads = get_posts( $args );

				foreach ( $downloads as $download ) : setup_postdata( $download );
					$id = $download->ID;
					$categories = get_the_terms( $id, 'download_category' );
					$tags = get_the_terms( $id, 'download_tag' );

					if( $bsfm_edd_prod_slug ) {
						$slug = $download->post_name;
						array_push( $m_tags, $slug);
					}

					if( $bsfm_edd_prod_cat ) {
						foreach ( $categories as $cat ) {
							array_push( $m_tags, $cat->name);
						}
					}

					if( $bsfm_edd_prod_tag ) {
						foreach ( $tags as $tag ) {
							array_push( $m_tags, $tag->name);
						}
					}
				endforeach;

				// General global config conditions
				$all_customer = $all_customer_ab = array(
					'add_segment' => array(),
					'remove_segment' => array()
				);
				array_push( $all_customer['add_segment'], $seg_action_id );
				array_push( $all_customer_ab['add_segment'], $seg_action_ab );

				$email = $payment_meta['user_info']['email'];
				$credentials = get_option( 'bsfm_mautic_credentials' );

				if( isset($_COOKIE['mtc_id']) ) {
					$contact_id = $_COOKIE['mtc_id'];
					$contact_id = (int)$contact_id;
				}
				else {
					$contact_id = self::bsfm_mautic_get_contact_by_email( $email, $credentials );
				}

				if( isset( $contact_id ) ) {
					$method = 'PATCH';
					$url = '/api/contacts/'.$contact_id.'/edit';
					//add to segment
					if( $new_status == 'abandoned' ) {
						$add_segment = $all_customer_ab['add_segment'];	
					}
					elseif( $new_status == 'publish' ) {
						$add_segment = $all_customer['add_segment'];
					}
					if( is_array( $add_segment ) ) {
						foreach ( $add_segment as $segment_id) {
							$segment_id = (int)$segment_id;
							$action = "add";
							$res = self::bsfm_mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action);
						}
					}
					// if staus is complete - remove user from abandoned segment
					// if( $new_status == 'publish' ) {
					//  $seg_action_ab = (int)$seg_action_ab;
					// 	$action = "remove";
					// 	$credentials = get_option( 'bsfm_mautic_credentials' );
					// 	$res = self::bsfm_mautic_contact_to_segment( $seg_action_ab, $contact_id, $credentials, $action);
					// }
				}
				else {
					$method = 'POST';
					$url = '/api/contacts/new';
				}

				$body = array(
					'firstname'	=>	$payment_meta['user_info']['first_name'],
					'lastname'	=>	$payment_meta['user_info']['last_name'],
					'email'		=>	$payment_meta['user_info']['email'],
				);

				if( isset($bsfm_edd_prod_cat) || isset($bsfm_edd_prod_slug) || isset($bsfm_edd_prod_tag) ) {
					if( is_array( $m_tags ) && ( sizeof( $m_tags )>0 ) ) {
						$m_tags = implode(",", $m_tags);
						$body['tags'] = $m_tags;
					}
				}

				if( ! isset( $contact_id ) ) {
					// Add all customers
					$ac_segment = $all_customer['add_segment'];
					if( isset( $seg_action_id ) && $new_status == 'publish' ) {
						if( is_array( $ac_segment ) && ( sizeof( $ac_segment )>0 ) ) {
							self::bsfm_mautic_api_call($url, $method, $body, $all_customer);
						}
					}

					// Abandoned Customers
					$ab_segment = $all_customer_ab['add_segment'];
					if( isset( $seg_action_ab ) && $new_status == 'abandoned' ) {
						if( is_array( $ab_segment ) && ( sizeof( $ab_segment )>0 ) ) {
							self::bsfm_mautic_api_call($url, $method, $body, $all_customer_ab);
							}
					}
				}
			}
		}

		public static function bsfm_filter_cf7_submit_fields($cf7) {
			$query = self::bsfm_create_query();
			if ( $query ) {
				self::bsfm_add_cf7_mautic( $query );
			}
			return $cf7;
		}

		public static function bsfm_create_query() {
			$query = array();
			if ( $submission = WPCF7_Submission::get_instance() ) {
				$query = $submission->get_posted_data();
			}
			return apply_filters( 'Bsfm_CF7_query_mapping', $query );
		}

		/** 
		 * Add cf7 submissions to Mautic contacts
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function bsfm_add_cf7_mautic( $query ) {
			if (!is_array($query)) return;
			$cf7_id = $query['_wpcf7'];
			$status = Bsfm_Postmeta::bsfm_get_cf7_condition( $cf7_id );
			if( is_array($status) && sizeof($status)>0 ) {
				$set_actions = Bsfm_Postmeta::bsfm_get_all_actions($status);
			}
			else {
				return;
			}
			foreach ($status as $rule) {
				$body_fields = self::bsf_get_cf7_mautic_fields_maping( $cf7_id, $rule, $query );
				$contact_id = '';
				if( !is_array($body_fields) ) {
					$email = $query['your-email'];
					$credentials = get_option( 'bsfm_mautic_credentials' );
					if( isset($_COOKIE['mtc_id']) ) {
						$contact_id = $_COOKIE['mtc_id'];
						$contact_id = (int)$contact_id;
					}
					else {
						$contact_id = self::bsfm_mautic_get_contact_by_email( $email, $credentials );
					}
				}
				
				if( isset( $contact_id ) ) {
					$method = 'PATCH';
					$url = '/api/contacts/'.$contact_id.'/edit';
					//add to segment
					$add_segment = $set_actions['add_segment'];
					if( is_array( $add_segment ) ) {
						foreach ( $add_segment as $segment_id) {
							$segment_id = (int)$segment_id;
							$action = "add";
							$res = self::bsfm_mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action);
						}
					}
				}
				else {
					$method = 'POST';
					$url = '/api/contacts/new';
				}

				if( !is_array($body_fields) ) {
					$body = array(
					'firstname'	=> $query['your-name'],
					'email'		=> $query['your-email']
					);
				}
				else {
					$body = $body_fields;
				}
				self::bsfm_mautic_api_call( $url, $method, $body, $set_actions);

				$remove_segment = $set_actions['remove_segment'];
				if( is_array( $remove_segment ) && ( sizeof($remove_segment)>0 ) ) {
					self::bsfm_remove_contact_from_segment( $body, $set_actions );
				}
				$add_segment = $set_actions['add_segment'];
				if( is_array( $add_segment ) && ( sizeof( $add_segment )>0 ) ) {
					self::bsfm_mautic_api_call($url, $method, $body, $set_actions);
				}
			}
		}

		/** 
		 * Map cf7 and Mautic contact fields
		 * 
		 * @since 1.0.0
		 * @return array 
		 */
		public static function bsf_get_cf7_mautic_fields_maping( $form_id, $rule_id, $query) {
			$meta_conditions = get_post_meta( $rule_id, 'bsfm_rule_condition' );
			if (isset($meta_conditions[0])) {
				$meta_conditions = unserialize($meta_conditions[0]);	
			}
			foreach ($meta_conditions as $meta_condition) {
				if( $meta_condition[0]=='CF7' && $meta_condition[1]==$form_id ) {
					$cf7_fields = $meta_condition[2]['cf7_fields'];
					$mautic_fields = $meta_condition[2]['mautic_cfields'];
				}
			}
			foreach ( $cf7_fields as $key => $field ) {
				$mapping[$mautic_fields[$key]] = $query[$field];
			}
			return $mapping;
		}

		/** 
		 * Add contacts to Mautic, Add to segments, return GET request data
		 * 
		 * @since 1.0.0
		 */
		public static function bsfm_mautic_api_call( $url, $method, $param = array(), $segments = array() ) {
			$status = 'success';
			$credentials = get_option( 'bsfm_mautic_credentials' );
			if(!isset($credentials['expires_in'])) {
				return;
			}
			// if token expired, get new access token
			if( $credentials['expires_in'] < time() ) {
				$grant_type = 'refresh_token';
				$response = BSFMauticAdminSettings::bsf_mautic_get_access_token( $grant_type );
				if ( is_wp_error( $response ) ) {
					$errorMsg = $response->get_error_message();
					$status = 'error';
					echo __( 'THERE APPEARS TO BE AN ERROR WITH THE CONFIGURATION.', 'bsfmautic' );
				} else {
					$access_details = json_decode( $response['body'] );
					$expiration = time() + $access_details->expires_in;
					$credentials['access_token'] = $access_details->access_token;
					$credentials['expires_in'] = $expiration;
					$credentials['refresh_token'] = $access_details->refresh_token;
					update_option( 'bsfm_mautic_credentials', $credentials );
				}
			} // refresh code token ends
			// add contacts
			$credentials = get_option( 'bsfm_mautic_credentials' );
			$access_token = $credentials['access_token'];
			$param['access_token'] = $access_token;
			$url = $credentials['baseUrl'] . $url;
			if( $method=="GET" ) {
				$url = $url .'?access_token='. $access_token;
				$response = wp_remote_get( $url );
				if( is_array($response) ) {
					$response_body = $response['body'];
					$body_data = json_decode($response_body);
					return $body_data;
						$response_code = $response['response']['code'];
						if( $response_code != 201 ) {
							if( $response_code != 200 ) {
								$ret = false;
								$status = 'error';
								$errorMsg = isset( $response['response']['message'] ) ? $response['response']['message'] : '';
								echo __( 'THERE APPEARS TO BE AN ERROR WITH THE CONFIGURATION.', 'bsfmautic' );
								return;
							}
						}
				}
			}
			else if( $method=="POST" || $method=="PATCH" ) {	// add new contact to mautic request

				$param['ipAddress'] = $_SERVER['REMOTE_ADDR'];
				$response = wp_remote_post( $url, array(
					'method' => $method,
					'timeout' => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(),
					'body' => $param,
					'cookies' => array()
				));
			}

			if ( is_wp_error( $response ) ) {
				$errorMsg = $response->get_error_message();
				$status = 'error';
				echo __( 'THERE APPEARS TO BE AN ERROR WITH THE CONFIGURATION.', 'bsfmautic' );
			} else {
				if( is_array($response) ) {
					$response_code = $response['response']['code'];
					if( $response_code != 201 ) {
						if( $response_code != 200 ) {
							$ret = false;
							$status = 'error';
							$errorMsg = isset( $response['response']['message'] ) ? $response['response']['message'] : '';
							echo __( 'THERE APPEARS TO BE AN ERROR WITH THE CONFIGURATION.', 'bsfmautic' );
						}
					} else {
						$response_body = $response['body'];
						$contact_created = json_decode($response_body);
						$contact = $contact_created->contact;
						/**
						 * if contact is created add to segment here
						 */
						if( isset($contact->id) ) {
							$contact_id =  (int)$contact->id;
							// fetch segment_id from rule and add contact to segment
							$add_segment = $segments['add_segment'];
							if( is_array( $add_segment ) ) {
								foreach ( $add_segment as $segment_id) {
									$segment_id = (int)$segment_id;
									$action = "add";
									$res = self::bsfm_mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action);
								}
							}
							$status = $res['status'];
							$errorMsg  = $res['error_message'];
						}
					}
				}
			}
		}

		/** 
		 * Remove contacts from segment
		 * 
		 * @since 1.0.0
		 */
		static function bsfm_remove_contact_from_segment( $param = array(), $set_actions = array() ) {
			//Remove contacts from segments
			$action = "remove";
			$email = $param['email'];
			$remove_segment = $set_actions['remove_segment'];
			$add_segment = $set_actions['add_segment'];
			$credentials = get_option( 'bsfm_mautic_credentials' );

			if( isset($_COOKIE['mtc_id']) ) {
				$contact_id = $_COOKIE['mtc_id'];
				$contact_id = (int)$contact_id;
			}
			else {
				$contact_id = self::bsfm_mautic_get_contact_by_email( $email, $credentials );
			}

			foreach ( $remove_segment as $segment_id) {
				$segment_id = (int)$segment_id;
				if( isset( $contact_id ) ) {
					$res = self::bsfm_mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action);
					$status = $res['status'];
					$errorMsg  = $res['error_message'];
				}
			}
			if( is_array( $add_segment ) ) {
				$action = "add";
				foreach ( $add_segment as $segment_id) {
					$segment_id = (int)$segment_id;
					if( isset( $contact_id ) ) {
						$res = self::bsfm_mautic_contact_to_segment( $segment_id, $contact_id, $credentials, $action);
					}
				}
			}
			return;
		}

		/** 
		 * Add contacts to segment
		 * 
		 * @since 1.0.0
		 */
		static function bsfm_mautic_contact_to_segment( $segment_id, $contact_id, $mautic_credentials, $act) {
			$errorMsg = '';
			$status = 'error';
			if( is_int($segment_id) && is_int($contact_id) ) {
				$url = $mautic_credentials['baseUrl'] . "/api/segments/".$segment_id."/contact/".$act."/".$contact_id;
				$access_token = $mautic_credentials['access_token'];
				$body = array(
					"access_token" => $access_token
				);
				$response = wp_remote_post( $url, array(
					'method' => 'POST',
					'timeout' => 45,
					'redirection' => 5,
					'httpversion' => '1.0',
					'blocking' => true,
					'headers' => array(),
					'body' => $body,
					'cookies' => array()
				)
				);
				if ( is_wp_error( $response ) ) {
					$errorMsg = $response->get_error_message();
					$status = 'error';
				} else {
					if( is_array($response) ) { 							
						$response_code = $response['response']['code'];
						if( $response_code != 200 ) {
							$status = 'error';
							$errorMsg = isset( $response['response']['message'] ) ? $response['response']['message'] : '';
						} else {
							$status = 'success';
						}
					}
				}
			}
			$response = array(
				'status' => $status,
				'error_message' => $errorMsg            
			);
			return $response;
		}

		/** 
		 * Get Mautic contact ID
		 * @return mautic contact id 
		 * @since 1.0.0
		 */
		static function bsfm_mautic_get_contact_by_email( $email, $mautic_credentials ) {
			$errorMsg = '';
			$status = 'error';
			$access_token = $mautic_credentials['access_token'];
			$url = $mautic_credentials['baseUrl'] . '/api/contacts/?search='. $email .'&access_token='. $access_token;
			$response = wp_remote_get( $url );
			if( is_array($response) ) {
				$response_body = $response['body'];
				$body_data = json_decode($response_body);
				$contact = $body_data->contacts;
				$contact_id = $contact[0]->id;
				$response_code = $response['response']['code'];
				if( $response_code != 201 ) {
					if( $response_code != 200 ) {
						$ret = false;
						$status = 'error';
						$errorMsg = isset( $response['response']['message'] ) ? $response['response']['message'] : '';
						echo __( 'THERE APPEARS TO BE AN ERROR WITH THE CONFIGURATION.', 'bsfmautic' );
						return;
					}
				}
				return $contact_id;
			}
		}
	}
endif;