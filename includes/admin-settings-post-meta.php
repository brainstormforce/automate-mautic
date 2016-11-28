<div id="fl-bsfm-post-meta" class="fl-settings-form bsfm-config-fl-post-meta">
	<form id="mautic-config-form" action="#" method="post">
		<div class="fl-settings-form-content">
		<?php
			if( isset($_GET['action']) && $_GET['action']=='edit') {
				if(isset($_GET['post'])) {
					$post_id = $_GET['post'];
				}
				$meta_conditions = get_post_meta( $post_id, 'bsfm_rule_condition' );
				$meta_conditions = unserialize($meta_conditions[0]);
				$meta_actions = get_post_meta( $post_id, 'bsfm_rule_action' );
				$meta_actions = unserialize($meta_actions[0]);
		?>
				<div class="bsf-mautic-metabox">
					<div class="conditions">
						<div id="bsfm-sortable-condition" class="bsfm-item-wrap">
					<?php	
						foreach ($meta_conditions as $order => $meta_condition) :	
					?>
						<fieldset class="ui-state-new" id="item-<?php echo $order; ?>">
							<span class="dashicons dashicons-minus remove-item"></span>
							<span class="dashicons dashicons-editor-justify sort-items"></span> 
							<select class="select-condition form-control" name="pm_condition[]">
								<option value="UR" <?php selected( $meta_condition[0],'UR' ); ?> >User Register on WordPress</option>
								<option value="CP" <?php selected( $meta_condition[0],'CP' ); ?> >User Post a Comment</option>
								<option value="CF7" <?php selected( $meta_condition[0],'CF7' ); ?> >User Submit Contact Form 7</option>
							</select>
							<?php	if( $meta_condition[0]=='CP' ) :	?>
									<div class="first-condition" style="display:inline;">
										<select id="sub-cp-condition" class="sub-cp-condition form-control" name="sub_cp_condition[]">
											<option value="ao_website" <?php selected( $meta_condition[1],'ao_website' ); ?> >Anywhere on website</option>
											<option value="os_page" <?php selected( $meta_condition[1],'os_page' ); ?> >On Specific Page</option>
											<option value="os_post" <?php selected( $meta_condition[1],'os_post' ); ?> >On Specific Post</option>
										</select>
									</div>
									<div class="second-condition" style="display:inline;">
										<?php
											if($meta_condition[1]=='os_page') {
												Bsfm_Postmeta::select_all_pages($meta_condition[2]);
											}
											elseif($meta_condition[1]=='os_post') {
												Bsfm_Postmeta::select_all_posts($meta_condition[2]);
											}									
								echo '</div>';
								endif;
								if( $meta_condition[0]=='CF7' ) : 
									$cf7_id = $meta_condition[1];
									?>
									<div class="first-condition" style="display:inline;">
										<?php Bsfm_Postmeta::select_all_cf7forms($cf7_id); ?>
									</div>
									<div class="second-condition" style="display:inline;">

						<!-- <table style='float: right;'><tbody>
							<tr>
								<td>
									<select class='mautic_form' name='cf7_fields[]'>

										<table style="float: right;">
											<tbody>
												   <tr>
												     <td>
												        <select class="mautic_form" name="cf7_fields[]">
												           <option value="your-name">your-name</option>
												           <option value="your-email">your-email</option>
												           <option value="your-message">your-message</option>
												        </select>
												     </td>
												  </tr> -->
									<?php
											$cf7_fields = Bsfm_Postmeta::make_cf7_fields( $cf7_id, 'your-name');
											print_r($cf7_fields);
													echo $cf7_fields['selHtml'];

											//cf7-id pass mapping func
											//bui;d table
										
											if($meta_condition[1]=='os_page') {
												Bsfm_Postmeta::select_all_pages($meta_condition[2]);
											}								
									echo '</div>';
								endif;
						echo '</fieldset>';
						endforeach;
						?>
					</div>
					<fieldset class="bsfm-add-condition add-new-item">
						<div class="ui-state-disabled">
							<span class="dashicons dashicons-plus-alt"></span><?php _e( 'Add new condition', 'bsfmautic' ); ?>
						</div>
					</fieldset>
					</div>
						<div class="actions">
							<h4> Action </h4>
							<div id="bsfm-sortable-action" class="bsfm-item-wrap">
							<?php	
								foreach ($meta_actions as $order => $meta_action) :	
							?>
								<fieldset class="ui-state-default">
									<span class="dashicons dashicons-minus remove-item"></span>
									<span class="dashicons dashicons-editor-justify sort-items"></span> 
										<select class="select-action form-control" name="pm_action[]">
											<option value="segment" <?php selected( $meta_action[0],'segment' ); ?> >Segment</option>
										</select>
							<?php if($meta_action[0]=='segment') :	?>
									<div class="first-action" style="display:inline;">
										<select id="sub-cp-action" class="sub-cp-action form-control" name="sub_seg_action[]">
											<option value="pre_segments" <?php selected( $meta_action[1],'pre_segments' ); ?> >Select predefined segment</option>
										</select>
									</div>
							<?php 
								endif;
								if($meta_action[1]=='pre_segments') :
							?>
									<div class="second-action" style="display:inline;">
										<?php Bsfm_Postmeta::select_all_segments($meta_action[2]); ?>
									</div>
							<?php	endif;	?>
								</fieldset>
							<?php
								endforeach;
							?>
							</div>				 
							<fieldset class="bsfm-add-action add-new-item">
								<div class="ui-state-disabled">
									<span class="dashicons dashicons-plus-alt"></span><?php _e( 'Add new action', 'bsfmautic' ); ?>
								</div>
							</fieldset>
						</div>
						<div class="actions">
							<h4> Select Method </h4>
							<fieldset class="select-method">
								<input type="radio" name="method" value="m_api" checked><span>Api</span> 
								<input type="radio" name="method" value="m_form"><span>Form</span>
							</fieldset>
						</div>
				</div>
				<?php
			}
			else {
			$bsfm 	=	BSF_Mautic_Helper::get_bsfm_mautic();
			$bsfm_enabled_track = $bsfm_base_url = $bsfm_public_key = $bsfm_secret_key = $bsfm_callback_uri = $bsfm_enabled_track_img = '';
			if( is_array($bsfm) ) {
				$bsfm_enabled_track	= ( array_key_exists( 'bsfm-enabled-tracking', $bsfm ) && $bsfm['bsfm-enabled-tracking'] == 1 )  ? ' checked' : '';
				$bsfm_enabled_track_img	= ( array_key_exists( 'bsfm-enabled-tracking-img', $bsfm ) && $bsfm['bsfm-enabled-tracking-img'] == 1 )  ? ' checked' : '';				
				$bsfm_base_url = ( array_key_exists( 'bsfm-base-url', $bsfm ) ) ? $bsfm['bsfm-base-url'] : '';
				$bsfm_public_key = ( array_key_exists( 'bsfm-public-key', $bsfm ) ) ? $bsfm['bsfm-public-key'] : '';
				$bsfm_secret_key = ( array_key_exists( 'bsfm-secret-key', $bsfm ) ) ? $bsfm['bsfm-secret-key'] : '';
				$bsfm_callback_uri = ( array_key_exists( 'bsfm-callback-uri', $bsfm ) ) ? $bsfm['bsfm-callback-uri'] : '';
			}
		?>
			<!-- default fields -->
			<div class="bsf-mautic-metabox">
				<div class="conditions">
					<h4> Conditions </h4>
					<div id="bsfm-sortable-condition" class="bsfm-item-wrap">
						<fieldset class="ui-state-default" id="item-1">
							<span class="dashicons dashicons-minus remove-item"></span>
							<span class="dashicons dashicons-editor-justify sort-items"></span> 
							<select class="select-condition form-control" name="pm_condition[]">
								<option value="UR">User Register on WordPress</option>
								<option value="CP">User Post a Comment</option>
								<option value="CF7">User Submit Contact Form 7</option>
							</select>
							<div class="first-condition" style="display:inline;"></div>
							<div class="second-condition" style="display:inline;"></div>
						</fieldset>
					</div>				 
					<fieldset class="bsfm-add-condition add-new-item">
						<div class="ui-state-disabled">
							<span class="dashicons dashicons-plus-alt"></span><?php _e( 'Add new condition', 'bsfmautic' ); ?>
						</div>
					</fieldset>
				</div>
				<div class="actions">
					<h4> Action </h4>
					<div id="bsfm-sortable-action" class="bsfm-item-wrap">
						<fieldset class="ui-state-default">
							<span class="dashicons dashicons-minus remove-item"></span>
							<span class="dashicons dashicons-editor-justify sort-items"></span> 
								<select class="select-action form-control" name="pm_action[]">
									<option value="segment">Segment</option>
								</select>
							<div class="first-action" style="display:inline;"></div>
							<div class="second-action" style="display:inline;"></div>
						</fieldset>
					</div>				 
					<fieldset class="bsfm-add-action add-new-item"><div class="ui-state-disabled"><span class="dashicons dashicons-plus-alt"></span> Add new action</div></fieldset>
				</div>
				<div class="actions">
					<h4> Select Method </h4>
					<fieldset class="select-method">
						<input type="radio" name="method" value="m_api" checked><span>Api</span> 
						<input type="radio" name="method" value="m_form"><span>Form</span>
					</fieldset>
				</div>
			</div>
			<!-- default fields end -->
		<?php	}	?>
		</div>
		<?php wp_nonce_field('bsfmauticpmeta', 'bsf-mautic-post-meta'); ?>
	</form>
</div>