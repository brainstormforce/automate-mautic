<div id="bsfm-bsfm-post-meta" class="bsfm-settings-form bsfm-config-fl-post-meta">
	<form id="bsfm-post-meta-form" action="#" method="post">
		<div class="bsfm-settings-form-content">
			<?php
			if( isset($_GET['action']) && $_GET['action']=='edit') {
				if(isset($_GET['post'])) {
					$post_id = $_GET['post'];
				}
				$meta_conditions = get_post_meta( $post_id, 'bsfm_rule_condition' );
				if (isset($meta_conditions[0])) {
					$meta_conditions = unserialize($meta_conditions[0]);	
				}
				$meta_actions = get_post_meta( $post_id, 'bsfm_rule_action' );
				if (isset($meta_actions[0])) {
					$meta_actions = unserialize($meta_actions[0]);
				}
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
								<option value="UR" <?php selected( $meta_condition[0],'UR' ); ?> ><?php _e( 'User Register on WordPress', 'bsfmautic' ) ?></option>
								<option value="CP" <?php selected( $meta_condition[0],'CP' ); ?> ><?php  _e( 'User Post a Comment', 'bsfmautic' ) ?></option>
								<option value="CF7" <?php selected( $meta_condition[0],'CF7' ); ?> ><?php _e( 'User Submit Contact Form 7', 'bsfmautic' ) ?></option>
								<option value="EDD" <?php selected( $meta_condition[0],'EDD' ); ?> ><?php _e( 'Easy Digital Downloads Purchase', 'bsfmautic' ) ?></option>
							</select>
							<?php	if( $meta_condition[0]=='CP' ) :	?>
									<div class="first-condition" style="display:inline;">
										<select id="sub-cp-condition" class="sub-cp-condition form-control" name="sub_cp_condition[]">
											<option value="ao_website" <?php selected( $meta_condition[1],'ao_website' ); ?> ><?php _e( 'Anywhere on website', 'bsfmautic' ) ?></option>
											<option value="os_page" <?php selected( $meta_condition[1],'os_page' ); ?> ><?php _e( 'On Specific Page', 'bsfmautic' ) ?></option>
											<option value="os_post" <?php selected( $meta_condition[1],'os_post' ); ?> ><?php _e( 'On Specific Post', 'bsfmautic' ) ?></option>
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
									//get cf7 data
									$cf7_field_data = get_post_meta( $cf7_id, '_form' );
									$reg = '/(?<=\[)([^\]]+)/';
									$str = $cf7_field_data[0];
									preg_match_all($reg, $str, $matches);
									array_pop($matches[0]);
									$map_cf7fields = sizeof( $matches[0] );
									?>
									<div class="first-condition" style="display:inline;">
										<?php Bsfm_Postmeta::select_all_cf7forms($cf7_id); ?>
									</div>
									<div class="second-condition" style="display:inline;">
										<table style="float: right;">
											<tbody>
												<?php
												foreach( $meta_condition[2]['mautic_cfields'] as $mform_field ) {
													echo '<tr><td>';
													echo '<select class="mautic_forms" name="mautic_cfields['.$cf7_id.'][]">';
														Bsfm_Postmeta::mautic_get_all_cfields( $mform_field );
													echo '</select></td></tr>';
												}
												?>
											</tbody>
										</table>
										<!-- Fetch cf7 fields -->
										<table style="float: right;">
											<tbody>
											<?php
											foreach( $meta_condition[2]['cf7_fields'] as $form_field ) {
										 		$cf7_fields = '<tr><td><select class="cf7_form" name="cf7_fields['.$cf7_id.'][]">';
												foreach ($matches[0] as $value) {
													$field = explode(' ',$value);
													$cf7_fields.= Bsfm_Postmeta::make_option($field[1], $field[1], $form_field);
												}
												$cf7_fields.= "</select></td></tr>";
												echo $cf7_fields;	
											}	
										echo '</tbody></table>';
									echo '</div>';
								endif;

								if( $meta_condition[0]=='EDD' ) : ?>
									<div class="first-condition" style="display:inline;">
										<?php Bsfm_Postmeta::select_all_edd_downloads( $meta_condition[1] ); ?>
									</div>
									<div class="second-condition" style="display:inline;">
										<?php Bsfm_Postmeta::bsf_make_edd_payment_status( $meta_condition[2] );
										$edd_prices = edd_get_variable_prices( $meta_condition[1] );
										$edd_vprice_sel = "<select class='edd_var_price' name='ss_edd_var_price[]'>";
										if( $edd_prices ) {
											foreach( $edd_prices as $price_id => $price ) {
												$edd_vprice_sel.= Bsfm_Postmeta::make_option($price_id , $price['name'], $meta_condition[3]);
											}
										}
										$edd_vprice_sel .= "</select>";
										echo $edd_vprice_sel;
									?>
									</div>
								<?php
								endif;
								if( $meta_condition[0]=='UR' ) :
									echo '<div class="first-condition" style="display:inline;"></div>';
									echo '<div class="second-condition" style="display:inline;"></div>';
								endif;
						echo '</fieldset>';
						endforeach;
						?>
					</div>
					<fieldset class="bsfm-add-condition add-new-item">
						<div>
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
											<option value="segment" <?php selected( $meta_action[0],'segment' ); ?> ><?php _e( 'Segment', 'bsfmautic' ) ?></option>
										</select>
							<?php if($meta_action[0]=='segment') :	?>
									<div class="first-action" style="display:inline;">
										<select id="sub-cp-action" class="sub-cp-action form-control" name="sub_seg_action[]">
											<option value="add_segment" <?php selected( $meta_action[1],'add_segment' ); ?> ><?php _e( 'Add to segment', 'bsfmautic' ) ?></option>
											<option value="remove_segment" <?php selected( $meta_action[1],'remove_segment' ); ?> ><?php _e( 'Remove from segment', 'bsfmautic' ) ?></option>
										</select>
									</div>
							<?php
								endif;
								if( $meta_action[1]=='add_segment' || $meta_action[1]=='remove_segment') :
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
								<div>
									<span class="dashicons dashicons-plus-alt"></span><?php _e( 'Add new action', 'bsfmautic' ); ?>
								</div>
							</fieldset>
						</div>
						<div id="save-action">
							<input style="float: right;" type="button" name="refresh-mautic" id="refresh-mautic" value="Refresh Mautic Data" class="button">
							<span class="spinner"></span>
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
								<option value="UR"><?php _e( 'User Register on WordPress', 'bsfmautic' ) ?></option>
								<option value="CP"><?php _e( 'User Post a Comment', 'bsfmautic' ) ?></option>
								<option value="CF7"><?php _e( 'User Submit Contact Form 7', 'bsfmautic' ) ?></option>
								<option value="EDD"><?php _e( 'Easy Digital Downloads Purchase', 'bsfmautic' ) ?></option>
							</select>
							<div class="first-condition" style="display:inline;"></div>
							<div class="second-condition" style="display:inline;"></div>
						</fieldset>
					</div>				 
					<fieldset class="bsfm-add-condition add-new-item">
						<div>
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
									<option value="segment"><?php _e( 'Segment', 'bsfmautic' ) ?></option>
								</select>
							<div class="first-action" style="display:inline;">
								<select id="sub-cp-action" class="sub-cp-action form-control" name="sub_seg_action[]">
									<option value="add_segment"><?php _e( 'Add to segment', 'bsfmautic' ) ?></option>
									<option value="remove_segment"><?php _e( 'Remove from segment', 'bsfmautic' ) ?></option>
								</select>
							</div>
							<div class="second-action" style="display:inline;">
								<?php Bsfm_Postmeta::select_all_segments(); ?>
							</div>
						</fieldset>
					</div>				 
					<fieldset class="bsfm-add-action add-new-item"><div><span class="dashicons dashicons-plus-alt"></span><?php _e( 'Add new action', 'bsfmautic' ) ?></div></fieldset>
				</div>
			</div>
			<!-- default fields end -->
		<?php	}	?>
		</div>
		<?php wp_nonce_field('bsfmauticpmeta', 'bsf-mautic-post-meta'); ?>
	</form>
</div>