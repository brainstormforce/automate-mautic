<?php
/**
 * Rules Post Meta
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'APM_RulePanel' ) ) :
	
	class APM_RulePanel {

	private static $instance;

	/**
	* Initiator
	*/
	public static function instance() 
	{
		if ( ! isset( self::$instance ) ) {
			self::$instance = new APM_RulePanel();
			self::$instance->hooks();
			self::$instance->includes();
		}
		return self::$instance;
	}

	public function includes() 
	{
		require_once AUTOMATEPLUS_MAUTIC_PLUGIN_DIR . 'classes/class-apm-admin-ajax.php';
	}

	public function hooks() 
	{
		add_action( 'wp_trash_post', array( $this, 'bsfm_clean_condition_action' ) );
	}

	public static function bsf_mautic_metabox_view() 
	{
		APM_AdminSettings::render_form( 'post-meta' );
	}

	public static function make_option( $id, $value, $selected = null )
	{
		$selected = selected( $id, $selected, false );
		return '<option value="' . $id . '"' . $selected . '>' . $value . '</option>';
	}

	public static function select_all_pages( $select = null ) 
	{
		//get all pages
		$all_pages= '<select id="sub-sub-condition" class="root-cp-condition form-control" name="ss_cp_condition[]">';
		$pages = get_pages();
		$all_pages .= '<option>' . __( 'Select Page', 'automateplus-mautic-wp' ) . '</option>';		

		foreach ( $pages as $page ) {
			$all_pages .= self::make_option($page->ID, $page->post_title, $select);
		}
		$all_pages.='</select>';
		echo $all_pages;
	}

	public static function select_all_posts( $select = null ) 
	{
		//get all posts
		$all_posts = '<select id="ss-cp-condition" class="root-cp-condition form-control" name="ss_cp_condition[]">';
		$args = array( 'posts_per_page' => -1 );
		$posts = get_posts( $args );
		$all_posts .= '<option>' . __( 'Select Post', 'automateplus-mautic-wp' ) . '</option>';
		foreach ( $posts as $post ) : setup_postdata( $post );
			$all_posts .= self::make_option($post->ID, $post->post_title, $select);	
		endforeach; 
		$all_posts.='</select>';
		wp_reset_postdata();
		echo $all_posts;
	}

	public static function select_all_segments( $select = null ) 
	{
		//get all segments
		$segments_trans = get_transient( 'apm_all_segments' );
		if( $segments_trans ) {
			$segments = $segments_trans;
		}
		else {
			$url = "/api/segments";
			$method = "GET";
			$body = '';
			$segments = AP_Mautic_Api::ampw_mautic_api_call($url, $method, $body);
			set_transient( 'apm_all_segments', $segments , DAY_IN_SECONDS );
		}
		if( empty($segments) ) {
			echo __( 'THERE APPEARS TO BE AN ERROR WITH THE CONFIGURATION.', 'automateplus-mautic-wp' );
			return;
		}

		$all_segments = '<select class="root-seg-action" name="ss_seg_action[]">';
			$all_segments .= '<option>' . __( 'Select Segment', 'automateplus-mautic-wp' ) . '</option>';

			foreach( $segments->lists as $offset => $list ) {
				$all_segments .= self::make_option( $list->id, $list->name, $select);
			}
		$all_segments .= '</select>';
		echo $all_segments;
	}

	public static function bsfm_clean_condition_action( $post_id ) 
	{
		$post_type = get_post_type($post_id);
		if ( "bsf-mautic-rule" != $post_type ) return;
		delete_post_meta( $post_id, 'ampw_rule_condition');
		delete_post_meta( $post_id, 'ampw_rule_action');
	}

	/**
	 * check if rule is set
	 * @param comment data
	 * @return rule id array
	 */ 
	public static function get_comment_condition( $comment_data = array() ) 
	{
		$args = array( 'posts_per_page' => -1, 'post_status' => 'publish', 'post_type' => 'bsf-mautic-rule');
		$posts = get_posts( $args );
		$set_rules = array();
		foreach ( $posts as $post ) : setup_postdata( $post );
			$rule_id = $post->ID;
			$meta_conditions = get_post_meta( $rule_id, 'ampw_rule_condition' );
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

	public static function get_wpur_condition() 
	{
		$args = array( 'posts_per_page' => -1, 'post_status' => 'publish', 'post_type' => 'bsf-mautic-rule');
		$posts = get_posts( $args );
		$ur_rules = array();
		foreach ( $posts as $post ) : setup_postdata( $post );
			$rule_id = $post->ID;
			$meta_conditions = get_post_meta( $rule_id, 'ampw_rule_condition' );
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

	/**
	 * Get all rules and manipulate actions
	 * @param rule_id array
	 * @return actions array
	 */
	public static function get_all_actions( $rules = array() )
	{

		$all_actions = array(
			'add_segment' => array(),
			'remove_segment' => array(),
			'add_tag' => array()
		);

		foreach ( $rules as $rule ) :
			$rule_id = $rule;
			$meta_actions = get_post_meta( $rule_id, 'ampw_rule_action' );
			$meta_actions = unserialize($meta_actions[0]);

				foreach( $meta_actions as $order => $meta_action ) :
					if( $meta_action[0]=='add_segment' ){
						//make array of segment id's
						$segment_id = $meta_action[1];
						array_push($all_actions['add_segment'], $segment_id);
					}
					if( $meta_action[0]=='remove_segment' ){
						//make array of segment id's
						$segment_id = $meta_action[1];
						array_push($all_actions['remove_segment'], $segment_id);
					}
					if( $meta_action[0]=='add_tag' ){
						array_push( $all_actions['add_tag'], $meta_action[1]);
					}

				endforeach;
		endforeach;

		return $all_actions;
	}

	/**
	 * list all conditions
	 */
	public static function get_all_conditions_list( $select = '' )
	{
		$conditions = '<option>' . __( 'Select Condition', 'automateplus-mautic-wp' ) . '</option>
			<option value="UR" '.selected( $select, 'UR' ).'>' . __( 'User Register on WordPress', 'automateplus-mautic-wp' ) . '</option>
			<option value="CP" '.selected( $select, 'CP' ).'>' . __( 'User Post a Comment', 'automateplus-mautic-wp' ) . '</option>';

		$all_conditions = apply_filters('amp_mautic_conditions_list', $conditions, $select);
		echo $all_conditions;
	}

	/**
	 * list all actions
	 */
	public static function get_all_actions_list( $select = '' )
	{
		$actions = '<option value="add_segment" '.selected( $select, 'add_segment' ).'>' . __( 'Add to segment', 'automateplus-mautic-wp' ) . '</option>
			<option value="remove_segment" '.selected( $select, 'remove_segment' ).'>' . __( 'Remove from segment', 'automateplus-mautic-wp' ) . '</option>
			<option value="add_tag" '.selected( $select, 'add_tag' ).'>' . __( 'Add Tags', 'automateplus-mautic-wp' ) . '</option>';
		$all_actions = apply_filters('amp_mautic_actions_list', $actions);
		echo $all_actions;
	}

	/**
	 * list all actions
	 */
	public static function get_comment_condition_sublist( $select = '' )
	{
		$comment_sublist = '<option value="ao_website" '.selected( $select, 'ao_website' ).'>' . __( 'Anywhere On Website', 'automateplus-mautic-wp' ) . '</option>
			<option value="os_page" '.selected( $select, 'os_page' ).'>' . __( 'On Specific Page', 'automateplus-mautic-wp' ) . '</option>
			<option value="os_post" '.selected( $select, 'os_post' ).'>' . __( 'On Specific Post', 'automateplus-mautic-wp' ) . '</option>';
		$comment_sublist = apply_filters('amp_mautic_comment_condition_sublist', $comment_sublist);
		echo $comment_sublist;
	}
}
$APM_RulePanel = APM_RulePanel::instance();
endif;