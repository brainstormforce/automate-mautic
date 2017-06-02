<?php
/**
 * Load html data.
 *
 * @package automateplus-mautic
 * @since 1.0.0
 */

?>
<script type="text/html" id="tmpl-apm-template">
	<# if ( 'condition-field' === data.clas ) { #>
		<span class="dashicons dashicons-minus remove-item"></span>
		<span class="dashicons dashicons-editor-justify sort-items"></span>
			<?php APMautic_RulePanel::get_all_conditions_list(); ?>
		<div class="first-condition" style="display:inline;"></div>
		<div class="second-condition" style="display:inline;"></div>
	<# } #>
	<# if ( 'action-field' === data.clas ) { #>
		<span class="dashicons dashicons-minus remove-item"></span>
		<span class="dashicons dashicons-editor-justify sort-items"></span>
		<div class="first-action" style="display:inline;">
				<?php APMautic_RulePanel::get_all_actions_list(); ?>
		</div>
		<div class="second-action" style="display:inline;">
			<input type="hidden" name="pm_action[]" value="segment">
			<?php APMautic_RulePanel::select_all_segments(); ?>
		</div>
	<# } #>

	<# if( 'get-all-segments' === data.clas ) { #>
			<input type="hidden" name="pm_action[]" value="segment">
			<?php APMautic_RulePanel::select_all_segments(); ?>
	<# } #>

	<# if( 'sub-cp-condition' === data.clas ) { #>
		<select id="sub-cp-condition" class="sub-cp-condition form-control" name="sub_cp_condition[]">
			<?php APMautic_RulePanel::get_comment_condition_sublist(); ?>
		</select>
	<# } #>

	<# if( 'sub-tag-action' === data.clas ) { #>
		<input id="sub-tag-action" type="text" class="sub-tag-action form-control" placeholder="comma separated tags" name="ss_seg_action[]">
	<# } #>

	<# if( 'sub-point-action' === data.clas ) { #>
		<input id="sub-point-action" type="number" class="sub-point-action form-control" name="ss_seg_action[]">
	<# } #>

	<# if( 'os_page' === data.clas ) { #>
		<?php APMautic_RulePanel::select_all_pages(); ?>
	<# } #>
	<# if( 'os_post' === data.clas ) { #>
		<?php APMautic_RulePanel::select_all_posts(); ?>
	<# } #>
</script>
