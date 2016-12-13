<?php
/**
 * Rules Post Meta
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'Bsfm_Postmeta' ) ) :
	
	class Bsfm_Postmeta {

	private static $instance;

	/**
	* Initiator
	*/
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Bsfm_Postmeta();
			self::$instance->hooks();
			self::$instance->includes();
		}
		return self::$instance;
	}

	public function includes() {
		require_once BSF_MAUTIC_PLUGIN_DIR . 'classes/class-bsfm-admin-ajax.php';
	}

	public function hooks() {
		// add_action( 'save_post', array( $this, 'bsfm_update_post_meta' ), 10, 3 );
		// add_action( 'add_meta_boxes', array( $this, 'bsf_mautic_register_meta_box' ) );
		add_action( 'wp_trash_post', array( $this, 'bsfm_clean_condition_action' ) );
		add_action( 'admin_menu', array( $this, 'bsfm_remove_meta_boxes' ) );
	}
	public function bsfm_remove_meta_boxes() {
		remove_meta_box( 'slugdiv' , 'bsf-mautic-rule' , 'normal' ); 
	}

	/**
	 * Register meta box(es).
	 */
	// public function bsf_mautic_register_meta_box() {
		// add_meta_box( 'bsf-mautic-rule', __( 'Trigger and Actions', 'bsfmautic' ), array( $this, 'bsf_mautic_metabox_view' ), 'bsf-mautic-rule' );
		// add_meta_box( 'bsf-mautic-rule', __( 'Trigger and Actions', 'bsfmautic' ), array( $this, 'bsf_mautic_metabox_view' ), 'bsf-mautic' );
		// add_meta_box( 'bsf-mautic-rule', __( 'Trigger and Actions', 'bsfmautic' ), array( __CLASS__, 'bsf_mautic_metabox_view' ), 'normal' );
	// }
	public static function bsf_mautic_metabox_view() {
		BSFMauticAdminSettings::render_form( 'post-meta' );
	}

	// test MB
	public function add_meta_box_b( $id, $title, $callback, $context = 'normal', $priority = 'default', $callback_args = null ) {
		//$this->has_meta_boxes = true;
		add_meta_box( "mautipress_-{$id}", $title, $callback, null, $context, $priority, $callback_args );
	}

	public static function make_option( $id, $value, $selected = null ) {
		$selected = selected( $id, $selected, false );
		return '<option value="' . $id . '"' . $selected . '>' . $value . '</option>';
	}
	public static function select_all_pages( $select = null ) {
		//get all pages
		$all_pages= '<select id="sub-sub-condition" class="root-cp-condition form-control" name="ss_cp_condition[]">';
		$pages = get_pages();
		$all_pages .= '<option> Select Page </option>';
		foreach ( $pages as $page ) {
			$all_pages .= Bsfm_Postmeta::make_option($page->ID, $page->post_title, $select);
		}
		$all_pages.='</select>';
		echo $all_pages;
	}
	public static function select_all_posts( $select = null ) {
		//get all posts
		$all_posts = '<select id="ss-cp-condition" class="root-cp-condition form-control" name="ss_cp_condition[]">';
		$args = array( 'posts_per_page' => -1 );
		$posts = get_posts( $args );
		$all_posts .= '<option> Select Post </option>';
		foreach ( $posts as $post ) : setup_postdata( $post );
			$all_posts .= Bsfm_Postmeta::make_option($post->ID, $post->post_title, $select);	
		endforeach; 
		$all_posts.='</select>';
		wp_reset_postdata();
		echo $all_posts;
	}
	public static function select_all_segments( $select = null ) {
		//get all segments
		$segments_trans = get_transient( 'bsfm_all_segments' );
		if( $segments_trans ) {
			$segments = $segments_trans;
		}
		else {
			$url = "/api/segments";
			$method = "GET";
			$body = '';
			$segments = BSF_Mautic::bsfm_mautic_api_call($url, $method, $body);
			set_transient( 'bsfm_all_segments', $segments , DAY_IN_SECONDS );
		}
		if( empty($segments) ) {
			echo __( 'THERE APPEARS TO BE AN ERROR WITH THE CONFIGURATION.', 'bsfmautic' );
			return;
		}
		$all_segments = '<select class="root-seg-action" name="ss_seg_action[]">';
			$all_segments .= '<option> Select Segment </option>';
			foreach( $segments->lists as $offset => $list ) {
				$all_segments .= Bsfm_Postmeta::make_option( $list->id, $list->name, $select);
			}
		$all_segments .= '</select>';
		echo $all_segments;
	}
	public static function select_all_mforms( $select = null ) {
		$mforms_trans = get_transient( 'bsfm_all_mforms' );
		if( $mforms_trans ) {
			$forms = $mforms_trans;
		}
		else {
			//get all Mautic forms
			$url = "/api/forms";
			$method = "GET";
			$body = '';
			$forms = BSF_Mautic::bsfm_mautic_api_call($url, $method, $body);
			set_transient( 'bsfm_all_mforms', $forms, DAY_IN_SECONDS );
		}
		$forms = $forms->forms;
		$all_mforms = '<select class="mautic_form">';
			foreach( $forms as $offset => $form ) {
				$all_mforms .= Bsfm_Postmeta::make_option($form->id, $form->name, $select);
			}
		$all_mforms .= '</select>';
		echo $all_mforms;
	}

	//get all mautic custom fields
	public static function mautic_get_all_cfields( $select = null ) {
		$mautic_cfields_trans = get_transient( 'bsfm_all_cfields' );
		$all_mfields = '';
		if( $mautic_cfields_trans ) {
			$mautic_cfields = $mautic_cfields_trans;
		}
		else {
			//get all Mautic forms
			$url = "/api/contacts/list/fields";
			$method = "GET";
			$body = '';
			$mautic_cfields = BSF_Mautic::bsfm_mautic_api_call($url, $method, $body);
			set_transient( 'bsfm_all_cfields', $mautic_cfields, DAY_IN_SECONDS );
		}
		//get all mautic fields here
		if( $mautic_cfields ) {
			foreach ( $mautic_cfields as $key => $field ) {
				$all_mfields .= Bsfm_Postmeta::make_option( $field->alias, $field->alias, $select);
			}
		}
		echo $all_mfields;
	}
	//list all cf7 forms
	public static function select_all_cf7forms( $select = null ) {
		//get all contact forms
		if (class_exists( 'WPCF7_ContactForm' )) {
			$cf7html ="";
			if ( !is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
				$cf7html = "";
				$cf7html = __( 'Please activate Contact Form 7 plugin.', 'bsfmautic' );
				return false;
			} else {
				$cf7_args = array(
					'posts_per_page' => -1,
					'orderby' => 'title',
					'order' => 'ASC',
					'offset' => 0,
				);
				$forms = WPCF7_ContactForm::find( $cf7_args );
				$cf7html .= '<select name="sub_cf_condition[]" class="sub-cf-condition">';
				$cf7html .= '<option> Select Form </option>';
				foreach ( $forms as $form ) {
					$cf7html .= Bsfm_Postmeta::make_option($form->id(), $form->title(), $select);
				}
				$cf7html .= '</select>';
			}
		}
		else {
			$cf7html = "Please activate Contact Form 7 plugin first";
		}
		echo $cf7html;
	}
	public static function bsfm_clean_condition_action( $post_id ) {
		$post_type = get_post_type($post_id);
		if ( "bsf-mautic-rule" != $post_type ) return;
		delete_post_meta( $post_id, 'bsfm_rule_condition');
		delete_post_meta( $post_id, 'bsfm_rule_action');
	}

	/**
	* check if rule is set
	* @param comment data
	* @return rule id array
	*/ 
	public static function bsfm_get_comment_condition( $comment_data = array() ) {
		$args = array( 'posts_per_page' => -1, 'post_status' => 'publish', 'post_type' => 'bsf-mautic-rule');
		$posts = get_posts( $args );
		$set_rules = array();
		foreach ( $posts as $post ) : setup_postdata( $post );
			$rule_id = $post->ID;
			$meta_conditions = get_post_meta( $rule_id, 'bsfm_rule_condition' );
			$meta_conditions = unserialize($meta_conditions[0]);
				foreach ($meta_conditions as $order => $meta_condition) :	
					if( $meta_condition[0]=='CP' ) {
						if( $meta_condition[1] == 'ao_website' ) {
							// add rule_id into array
							array_push( $set_rules, $rule_id);
						}
						if( $meta_condition[1] == 'os_page' || $meta_condition[1] == 'os_post' ) {
							if( is_array($comment_data) && $meta_condition[2] == $comment_data['comment_post_ID'] ) {
								array_push($set_rules, $rule_id);
							}
						}
					}
				endforeach;
		endforeach;
		return $set_rules;
	}

	/**
	* check if rule is set
	* @param comment data
	* @return rule id array
	*/ 
	public static function bsfm_get_edd_condition( $payment_meta, $status ) {
		$args = array( 'posts_per_page' => -1, 'post_status' => 'publish', 'post_type' => 'bsf-mautic-rule' );
		$set_rules = $download_id = $price_id = array();
		$posts = get_posts( $args );

		foreach ( $payment_meta['downloads'] as $downloads ) {
			array_push( $download_id, $downloads['id']);
			array_push( $price_id, $downloads['options']['price_id']);
		}
		foreach ( $posts as $post ) : setup_postdata( $post );
			 	$rule_id = $post->ID;
				$meta_conditions = get_post_meta( $rule_id, 'bsfm_rule_condition' );
				$meta_conditions = unserialize($meta_conditions[0]);
				
					foreach  ($meta_conditions as $meta_condition ) :	
						if( $meta_condition[0]=='EDD' ) {
							if( in_array( $meta_condition[1], $download_id ) || $meta_condition[1] == 'all' ) {
								// status check 
								if( $status == $meta_condition[2] ) {
									if( in_array( $meta_condition[3], $price_id) || $meta_condition[1] == 'all' ) {
										array_push( $set_rules, $rule_id);
									}
								}
							}
						}
					endforeach;
		endforeach;
		return $set_rules;
	}

	public static function bsfm_get_wpur_condition() {
		$args = array( 'posts_per_page' => -1, 'post_status' => 'publish', 'post_type' => 'bsf-mautic-rule');
		$posts = get_posts( $args );
		$ur_rules = array();
		foreach ( $posts as $post ) : setup_postdata( $post );
			$rule_id = $post->ID;
			$meta_conditions = get_post_meta( $rule_id, 'bsfm_rule_condition' );
			$all_conditions = unserialize($meta_conditions[0]);
			foreach ($all_conditions as $meta_condition) :
				if( $meta_condition[0]=='UR' ) {
						// add rule_id into array
						array_push( $ur_rules, $rule_id);
				}
			endforeach;
		endforeach;
		return $ur_rules;
	}
	public static function bsfm_get_cf7_condition( $form_id ) {
		$args = array( 'posts_per_page' => -1, 'post_status' => 'publish', 'post_type' => 'bsf-mautic-rule');
		$posts = get_posts( $args );
		$cf7_rules = array();
		foreach ( $posts as $post ) : setup_postdata( $post );
			$rule_id = $post->ID;
			$meta_conditions = get_post_meta( $rule_id, 'bsfm_rule_condition' );
			$all_conditions = unserialize($meta_conditions[0]);
			foreach ($all_conditions as $meta_condition) {
				if( $meta_condition[0]=='CF7' ) {
					//add rule_id into array
					if( $meta_condition[1] == $form_id ) {
						array_push($cf7_rules, $rule_id);
					}
				}
			}
		endforeach;
		return $cf7_rules;
	}
	/**
	* Get all rules and manipulate actions
	* @param rule_id array
	* @return actions array
	*/
	public static function bsfm_get_all_actions( $rules = array() ) {
		$all_actions = array(
			'add_segment' => array(),
			'remove_segment' => array()
		);
		foreach ( $rules as $rule ) :
			$rule_id = $rule;
			$meta_actions = get_post_meta( $rule_id, 'bsfm_rule_action' );
			$meta_actions = unserialize($meta_actions[0]);
				foreach($meta_actions as $order => $meta_action) :
					if( $meta_action[0]=='segment' ){
						if( $meta_action[1]=='add_segment' ){
							//make array of segment id's
							$segment_id = $meta_action[2];
							array_push($all_actions['add_segment'], $segment_id);
						}
						if( $meta_action[1]=='remove_segment' ){
							//make array of segment id's
							$segment_id = $meta_action[2];
							array_push($all_actions['remove_segment'], $segment_id);
						}
					}
				endforeach;
		endforeach;
		return $all_actions;
	}

	/** 
	 * Get all EDD products
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function select_all_edd_downloads( $select = null ) {
		$args = array( 'post_type'	=>	'download', 'posts_per_page' => -1, 'post_status' => 'publish' );
		$downloads = get_posts( $args );
		$all_downloads = '<select id="sub-edd-condition" class="sub-edd-condition form-control" name="sub_edd_condition[]">';
		$all_downloads .= '<option> Select Product </option>';
		$all_downloads .= Bsfm_Postmeta::make_option('all', 'Any Product', $select);
			foreach ( $downloads as $download ) : setup_postdata( $download );
				$all_downloads .= Bsfm_Postmeta::make_option($download->ID, $download->post_title, $select);	
			endforeach;
		$all_downloads .='</select>';
		wp_reset_postdata();
		echo $all_downloads;
	}

	/** 
	 * Get all EDD payment status
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function bsf_make_edd_payment_status( $select = null ) {
		$status_val = array( 'publish', 'refunded', 'revoked', 'failed', 'abandoned');
		$status_label = array( 'Completed', 'Refunded', 'Revoked', 'Failed', 'Abandoned');
		$select_status = '<select id="sub-sub-condition" class="root-edd-condition form-control" name="ss_edd_condition[]">';
		$select_status .= '<option> Select Payment Status</option>';
		foreach ( $status_val as $key => $payment_status ) :
			$select_status .= Bsfm_Postmeta::make_option($payment_status, $status_label[$key], $select);
		endforeach;
		$select_status .= '</select>';
		echo $select_status;
	}
}
$Bsfm_Postmeta = Bsfm_Postmeta::instance();
endif;