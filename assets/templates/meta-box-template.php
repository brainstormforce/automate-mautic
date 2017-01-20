<script type="text/html" id="tmpl-apm-template">
	<# if ( 'condition-field' === data.clas ) { #>
		<span class="dashicons dashicons-minus remove-item"></span>
		<span class="dashicons dashicons-editor-justify sort-items"></span>
		<select class="select-condition form-control" name="pm_condition[]">
			<?php APM_RulePanel::get_all_conditions_list(); ?>
		</select>
		<div class="first-condition" style="display:inline;"></div>
		<div class="second-condition" style="display:inline;"></div>
	<# } #>
	<# if ( 'action-field' === data.clas ) { #>
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
	<# } #>

	<# if( 'get-all-segments' === data.clas ) { #>
			<input type="hidden" name="pm_action[]" value="segment">
			<?php APM_RulePanel::select_all_segments(); ?>
	<# } #>

	<# if( 'sub-cp-condition' === data.clas ) { #>
		<select id="sub-cp-condition" class="sub-cp-condition form-control" name="sub_cp_condition[]">
			<?php APM_RulePanel::get_comment_condition_sublist(); ?>
		</select>
	<# } #>

	<# if( 'sub-tag-action' === data.clas ) { #>
		<input id="sub-tag-action" type="text" class="sub-tag-action form-control" placeholder="comma separated tags" name="ss_seg_action[]">
	<# } #>

	<# if( 'sub-point-action' === data.clas ) { #>
		<input id="sub-point-action" type="number" class="sub-point-action form-control" name="ss_seg_action[]">
	<# } #>

	<# if( 'os_page' === data.clas ) { #>
		<?php APM_RulePanel::select_all_pages(); ?>
	<# } #>
	<# if( 'os_post' === data.clas ) { #>
		<?php APM_RulePanel::select_all_posts(); ?>
	<# } #>
</script>
