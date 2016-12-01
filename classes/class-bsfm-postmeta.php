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
		}
		return self::$instance;
	}
 
	public function hooks() {
		add_action( 'save_post', array( $this, 'bsfm_update_post_meta' ), 10, 3 );
		add_action( 'add_meta_boxes', array( $this, 'bsf_mautic_register_meta_box' ) );
		add_action( 'wp_trash_post', array( $this, 'bsfm_clean_condition_action' ) );
		add_action( 'wp_ajax_get_cf7_fields', array( $this, 'bsf_make_cf7_fields' ) );
		add_action( 'wp_ajax_get_edd_var_price', array( $this, 'bsf_get_edd_variable_price' ) );
		//	check if plugin active
		//	filter to get all payment status
		//	add_filters( 'edd_payments_table_views', array( $this, 'bsf_make_edd_payment_status' ), 8 );
	}
	/**
	* Register meta box(es).
	*/
	public function bsf_mautic_register_meta_box() {
		add_meta_box( 'bsf-mautic-rule', __( 'Conditions and Actions', 'bsfmautic' ), array( $this, 'bsf_mautic_metabox_view' ), 'bsf-mautic-rule' );
	}
	public function bsf_mautic_metabox_view( $post ) {
		BSFMauticAdminSettings::render_form( 'post-meta' );
	}
	public static function make_option( $id, $value, $selected = null ) {
		$selected = selected( $id, $selected, false );
		return '<option value="' . $id . '"' . $selected . '>' . $value . '</option>';
	}
	public static function select_all_pages( $select = null ) {
		//get all pages
		$all_pages= '<select id="sub-sub-condition" class="root-cp-condition form-control" name="ss_cp_condition[]">';
		$pages = get_pages();
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
	public static function get_all_cf7_fields( $cf7_id = null, $select = null ) {
		// duplicate function
		$cf7_field_data = get_post_meta( $cf7_id, '_form' );
		$reg = '/(?<=\[)([^\]]+)/';
		$str = $cf7_field_data[0];
		preg_match_all($reg, $str, $matches);
		$map_cf7fields = sizeof($matches[0]);
		$cf7_fields = "<tr><td><select>";
		foreach ($matches[0] as $value) {
			$field = explode(' ',$value);
			$cf7_fields.= Bsfm_Postmeta::make_option($field[1], $field[1], $select);
		}
		$cf7_fields.= "</select></td></tr>";
		$fields_return = array(
				'fieldCount' => $map_cf7fields,
				'selHtml' => $cf7_fields
		);
		return $fields_return;
	}
	public static function bsf_make_cf7_fields( $cf7_id='', $select='' ) {
		//get all contact form fields
		$cf7_id = $_POST['cf7Id'];
		$cf7_field_data = get_post_meta( $cf7_id, '_form' );
		$reg = '/(?<=\[)([^\]]+)/';
		$str = $cf7_field_data[0];
		preg_match_all($reg, $str, $matches);
		array_pop($matches[0]);
		$map_cf7fields = sizeof($matches[0]);
		$cf7_fields = "<table style='float: right;'><tbody>";
		$cf7_fields_sel = "<tr><td><select class='mautic_form' name='cf7_fields[$cf7_id][]'>";
		foreach ($matches[0] as $value) {
			$field = explode(' ',$value);
			$cf7_fields_sel.= Bsfm_Postmeta::make_option($field[1], $field[1], $select);
		}
		$cf7_fields_sel.= "</select></td></tr>";
		for ( $i=0; $i < $map_cf7fields; $i++) { 
			$cf7_fields.= $cf7_fields_sel;
		}
		$cf7_fields.= "</tbody></table>";
		print_r(json_encode( array(
				'fieldCount' => $map_cf7fields,
				'selHtml' => $cf7_fields
		)));
		wp_die();
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
		foreach ( $mautic_cfields as $key => $field ) {
			$all_mfields .= Bsfm_Postmeta::make_option( $field->alias, $field->alias, $select);
		}
		echo $all_mfields;
	}
	//list all cf7 forms
	public static function select_all_cf7forms( $select = null ) {
		//get all contact forms
		if (class_exists( 'WPCF7_ContactForm' )) {
			$active_plugins = get_option( 'active_plugins' );
			$plugin = 'contact-form-7/wp-contact-form-7.php';
			$cf7html ="";
			if ( false === array_search( $plugin, $active_plugins ) || ! file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
				$cf7html = "Please activate Contact Form 7 plugin.";
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
	public static function bsfm_update_post_meta( $post_id, $post, $update ) {
		$post_type = get_post_type($post_id);
		if ( "bsf-mautic-rule" != $post_type ) return;
		if ( isset( $_POST['pm_condition'] ) ) {
			$conditions = $_POST['pm_condition'];
			$cp_keys = array_keys( $conditions, "CP");
			$cf7_keys = array_keys( $conditions, "CF7");
			$edd_keys = array_keys( $conditions, "EDD");
			$condition_cnt = sizeof( $conditions );
			for($i=0; $i < $condition_cnt; $i++) {
				if($conditions[$i]=='UR') {
					$update_conditions[$i] = array( $conditions[$i] );
				}
				if ($conditions[$i]=='CP') {
					$sub_key = array_search($i,$cp_keys);
					$update_conditions[$i] = array(
						$conditions[$i],
						$_POST['sub_cp_condition'][$sub_key], 
						$_POST['ss_cp_condition'][$sub_key] );
				}
				if ($conditions[$i] == "CF7") {
					$sub_key = array_search($i,$cf7_keys);
					$update_maping = '';
					$form_id = $_POST['sub_cf_condition'][$sub_key];
					$update_maping['cf7_fields'] = $_POST['cf7_fields'][$form_id];
					$update_maping['mautic_cfields'] = $_POST['mautic_cfields'][$form_id];
					$update_conditions[$i] = array(
						$conditions[$i],
						$_POST['sub_cf_condition'][$sub_key],
						$update_maping );
				}
				if ($conditions[$i] == "EDD") {
					$sub_key = array_search($i,$edd_keys);
					$update_maping = '';
					$download_id = $_POST['sub_edd_condition'][$sub_key];
					$update_conditions[$i] = array(
						$conditions[$i],
						$_POST['sub_edd_condition'][$sub_key],
						$_POST['ss_edd_condition'][$sub_key],
						$_POST['ss_edd_var_price'][$sub_key] );
				}
			}
			$update_conditions = serialize($update_conditions);
			update_post_meta( $post_id, 'bsfm_rule_condition', $update_conditions );
			// update data submit method
			$update_method = array(
				'method'		=>	$_POST['method'],
				'mautic_form_id'=>	$_POST['mautic_form_id'],
				'form_fields'	=>	$_POST['mautic_form_field']
			);
			$update_method = serialize($update_method);
			update_post_meta( $post_id, 'bsfm_mautic_method', $update_method );
		}
		//update actions
		if ( isset( $_POST['pm_action'] ) ) {
			$actions = $_POST['pm_action'];
			$seg_keys = array_keys( $actions, "segment");
			$action_cnt = sizeof($actions);
			for($i=0; $i < $action_cnt; $i++) {
				if($actions[$i]=='tag') {
					$update_actions[$i] = $actions[$i];
				}
				if($actions[$i]=='segment') {
					$sub_key = array_search($i,$seg_keys);
					$update_actions[$i] = array(
						$actions[$i],
						$_POST['sub_seg_action'][$sub_key],
						$_POST['ss_seg_action'][$sub_key]
					);
				}
			}
			$update_actions = serialize($update_actions);
			update_post_meta( $post_id, 'bsfm_rule_action', $update_actions );
		}
	}
	/**
	* check if rule is set
	* @param comment data
	* @return rule id array
	*/ 
	public static function bsfm_get_comment_condition( $comment_data = array() ) {
		/*
		@todo fetch all rules ID
		@todo check meta for comment post 1.page 2.post 3.anywhere
		@todo check correspondig action and return
		*/
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
	public static function bsfm_get_wpur_condition() {
		/*
			@todo fetch all rules ID
			@todo check meta for user register condition
			@todo return rule id array
		*/
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
		/*
		* @ todo get all rule_id 
		* @ fetch all actions for that rule
		* @ return Array
		*/
		$all_actions = array();
		foreach ( $rules as $rule ) :
			$rule_id = $rule;
			$meta_actions = get_post_meta( $rule_id, 'bsfm_rule_action' );
			$meta_actions = unserialize($meta_actions[0]);
				foreach($meta_actions as $order => $meta_action) :
					if( $meta_action[0]=='segment' ){
						if( $meta_action[1]=='pre_segments' ){
							//make array of segment id's
							$segment_id = $meta_action[2];
							array_push($all_actions, $segment_id);
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
		$args = array('post_type'	=>	'download', 'posts_per_page' => -1, 'post_status' => 'publish' );
		$downloads = get_posts( $args );
		$all_downloads = '<select id="sub-edd-condition" class="sub-edd-condition form-control" name="sub_edd_condition[]">';
		$all_downloads .= '<option> Select Download </option>';
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
		$status = array( 'publish ', 'pending ', 'refunded ', 'revoked ', 'failed ', 'abandoned ');
		$select_status = '<select id="sub-sub-condition" class="root-edd-condition form-control" name="ss_edd_condition[]">';
		foreach ( $status as $payment_status ) :
			$select_status .= Bsfm_Postmeta::make_option($payment_status, $payment_status, $select);
		endforeach;
		$select_status .= '</select>';
		echo $select_status;
	}

	/** 
	 * Get EDD - downloads variable price
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function bsf_get_edd_variable_price( $download_id='', $select='' ) {
		//get all contact form fields
		$download_id = $_POST['download_id'];
		$edd_prices = edd_get_variable_prices( $download_id );
		$edd_vprice_sel = "<select class='edd_var_price' name='ss_edd_var_price[]'>";
		if( $edd_prices ) {
			foreach( $edd_prices as $price_id => $price ) {
				$edd_vprice_sel.= Bsfm_Postmeta::make_option($price_id , $price['name'], $select);
			}
		}
		$edd_vprice_sel .= "</select>";
		echo $edd_vprice_sel;
		wp_die();
	}
}
$Bsfm_Postmeta = Bsfm_Postmeta::instance();
endif;