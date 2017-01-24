<?php
/**
 * Handle rules panel
 *
 * @package automateplus-mautic
 * @since 1.0.0
 */

?>
<div id="ampw-post-meta" class="apmw-settings-form wp-core-ui">
	<form id="ampw-post-meta-form" action="#" method="post">
		
		<?php
		if ( isset( $_GET['post'] ) ) {
			$post_id = esc_attr( $_GET['post'] );
			$rule_title = get_the_title( $post_id );
		} else {
			$rule_title = '';
		}
		?>
		<div class="wrap">
			<input type="text" name="ampw_rule_title" class="ampw_rule_title" value="<?php echo $rule_title; ?>" placeholder="Enter Rule Title">
		</div>
		<?php
		if ( isset( $_GET['action'] ) &&  'edit' == $_GET['action'] ) {
			if ( isset( $_GET['post'] ) ) {
				$post_id = esc_attr( $_GET['post'] );
			}
			$meta_conditions = get_post_meta( $post_id, 'ampw_rule_condition' );
			if ( isset( $meta_conditions[0] ) ) {
				$meta_conditions = unserialize( $meta_conditions[0] );
			}
			$meta_actions = get_post_meta( $post_id, 'ampw_rule_action' );
			if ( isset( $meta_actions[0] ) ) {
				$meta_actions = unserialize( $meta_actions[0] );
			}
		?>
		<div class="ampw-settings-form-content">
		
				<div class="ampw-mautic-metabox">
					<div class="conditions">
						<h4> <?php _e( 'Trigger', 'automateplus-mautic-wp' ) ?> </h4>
						<div id="ampw-sortable-condition" class="apmw-item-wrap">
					<?php
					if ( ! empty( $meta_conditions ) ) {
						foreach ( $meta_conditions as $order => $meta_condition ) :
					?>
						<fieldset class="ui-state-new" id="item-<?php echo $order; ?>">
							<span class="dashicons dashicons-minus remove-item"></span>
							<span class="dashicons dashicons-editor-justify sort-items"></span>
							<select class="select-condition form-control" name="pm_condition[]">
								<?php APM_RulePanel::get_all_conditions_list( $meta_condition[0] ); ?>
							</select>
							<?php

							if ( 'CP' == $meta_condition[0]  ) : ?>

								<div class="first-condition" style="display:inline;">
									<select id="sub-cp-condition" class="sub-cp-condition form-control" name="sub_cp_condition[]">
										<?php APM_RulePanel::get_comment_condition_sublist( $meta_condition[1] ); ?>
									</select>
								</div>
									<div class="second-condition" style="display:inline;">
										<?php
										if ( 'os_page' == $meta_condition[1] ) {
											APM_RulePanel::select_all_pages( $meta_condition[2] );
										} elseif ( 'os_post' == $meta_condition[1] ) {
											APM_RulePanel::select_all_posts( $meta_condition[2] );
										}
										echo '</div>';
								endif;

							if ( 'UR' == $meta_condition[0] ) :
								echo '<div class="first-condition" style="display:inline;"></div>';
								echo '<div class="second-condition" style="display:inline;"></div>';
								endif;
									$action = 'new_condition_' . $meta_condition[0];
									do_action( $action, $meta_condition );
							echo '</fieldset>';
						endforeach;
					}
						?>
					</div>
					<fieldset class="ampw-add-condition add-new-item">
						<div>
							<span class="dashicons dashicons-plus-alt ampw-new-item-icon"></span><?php _e( ' Add new condition', 'automateplus-mautic-wp' ); ?>
						</div>
					</fieldset>
					</div>
						<div class="actions">
							<h4> <?php _e( 'Action', 'automateplus-mautic-wp' ) ?> </h4>
							<div id="ampw-sortable-action" class="apmw-item-wrap">
							<?php
							if ( ! empty( $meta_actions ) ) {

								foreach ( $meta_actions as $order => $meta_action ) :
							?>
								<fieldset class="ui-state-new">
									<span class="dashicons dashicons-minus remove-item"></span>
									<span class="dashicons dashicons-editor-justify sort-items"></span> 
						
									<div class="first-action" style="display:inline;">
										<select id="sub-seg-action" class="sub-seg-action form-control" name="sub_seg_action[]">
											<?php APM_RulePanel::get_all_actions_list( $meta_action[0] ); ?>
										</select>
									</div>
							<?php
							if ( 'add_segment' == $meta_action[0] || 'remove_segment' == $meta_action[0] ) {
							?>
							<div class="second-action" style="display:inline;">
								<input type="hidden" name="pm_action[]" value="segment">
								<?php APM_RulePanel::select_all_segments( $meta_action[1] ); ?>
							</div>
							<?php } elseif ( 'add_tag' == $meta_action[0] ) { ?>
									<div class="second-action" style="display:inline;">
										<input type="hidden" name="pm_action[]" value="tag">
										<input type="text" name="ss_seg_action[]" value="<?php echo $meta_action[1]; ?>">
									</div>
							<?php
}
							?>
								</fieldset>
							<?php
							endforeach;
							}
							?>
							</div>
							<fieldset class="add-new-item">
								<div>
									<span class="dashicons dashicons-plus-alt ampw-add-action ampw-new-item-icon"></span><span class="ampw-add-action"><?php _e( ' Add new action', 'automateplus-mautic-wp' ); ?></span>
								</div>
							</fieldset>
						</div>
				</div>
			</div>

			<p class="submit">
				<input type="submit" value="Update Rule" class="button button-primary button-large" name="edit_the_rule">
				<a href="<?php APM_AdminSettings::render_page_url( '&tab=all_rules' ) ?>" ><?php _e( 'Back to All Rules', 'automateplus-mautic-wp' ); ?></a>
			</p>
			<?php wp_nonce_field( 'apmauticpmeta', 'apmw-mautic-post-meta-nonce' ); ?>
				<?php
		} // end edit
		else {
		?>
		<!-- default fields -->
		<div class="ampw-settings-form-content">
		<div class="ampw-mautic-metabox">
		<div class="conditions">
			<h4> <?php _e( 'Trigger', 'automateplus-mautic-wp' ) ?> </h4>
			<div id="ampw-sortable-condition" class="apmw-item-wrap">
				<fieldset class="ui-state-default" id="item-1">
					<span class="dashicons dashicons-minus remove-item"></span>
					<span class="dashicons dashicons-editor-justify sort-items"></span> 
					<select class="select-condition form-control" name="pm_condition[]">
						<?php APM_RulePanel::get_all_conditions_list(); ?>
					</select>
					<div class="first-condition" style="display:inline;"></div>
					<div class="second-condition" style="display:inline;"></div>
				</fieldset>
			</div>               
			<fieldset class="ampw-add-condition add-new-item">
				<div>
					<span class="dashicons dashicons-plus-alt ampw-new-item-icon"></span><?php _e( ' Add new condition', 'automateplus-mautic-wp' ); ?>
				</div>
			</fieldset>
		</div>
		<div class="actions">
			<h4> <?php _e( 'Action', 'automateplus-mautic-wp' ) ?> </h4>
			<div id="ampw-sortable-action" class="apmw-item-wrap">
				<fieldset class="ui-state-default">
					<span class="dashicons dashicons-minus remove-item"></span>
					<span class="dashicons dashicons-editor-justify sort-items"></span> 
					<div class="first-action" style="display:inline;">
						<select id="sub-seg-action" class="sub-seg-action form-control" name="sub_seg_action[]">
							<?php APM_RulePanel::get_all_actions_list(); ?>
						</select>
					</div>
					<div class="second-action" style="display:inline;">
						<input type="hidden" name="pm_action[]" value="segment">
						<?php APM_RulePanel::select_all_segments(); ?>
					</div>
				</fieldset>
			</div>               
				<fieldset class="add-new-item">
					<div>
						<span class="dashicons dashicons-plus-alt ampw-add-action ampw-new-item-icon"></span><span class="ampw-add-action"><?php _e( ' Add new action', 'automateplus-mautic-wp' ); ?></span>
					</div>
				</fieldset>
		</div>
		</div>
		<!-- default fields end -->
		</div>
		<p class="submit">
		<input type="submit" value="Add Rule" class="button button-primary button-large" name="add_new_rule">
		</p>
		<?php wp_nonce_field( 'apmauticpmeta', 'apmw-mautic-post-meta-nonce' ); ?>
		<?php }	?>
	</form>
</div>
