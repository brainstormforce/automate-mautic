<?php
/**
 * Load html data.
 *
 * @package automate-mautic
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
			<?php
			APMautic_Helper::render_input_html(
				'pm_action[]', array(
					'def_value' => 'segment',
					'type'      => 'hidden',
					'iswrap'    => false,
				)
			);
			APMautic_Services::select_all_segments();
			?>
		</div>
	<# } #>

	<# if( 'get-all-segments' === data.clas ) { #>
			<?php
			APMautic_Helper::render_input_html(
				'pm_action[]', array(
					'def_value' => 'segment',
					'type'      => 'hidden',
					'iswrap'    => false,
				)
			);
			APMautic_Services::select_all_segments();
			?>
	<# } #>

	<# if( 'sub-cp-condition' === data.clas ) { #>
			<?php APMautic_RulePanel::get_comment_condition_sublist(); ?>
	<# } #>

	<# if( 'sub-tag-action' === data.clas ) { #>
		<input id="sub-tag-action" type="text" class="sub-tag-action form-control" placeholder="Comma Separated Tags" name="ss_seg_action[]">
	<# } #>

	<# if( 'os_page' === data.clas ) { #>
		<?php APMautic_RulePanel::select_all_pages(); ?>
	<# } #>
	<# if( 'os_post' === data.clas ) { #>
		<?php APMautic_RulePanel::select_all_posts(); ?>
	<# } #>
</script>
