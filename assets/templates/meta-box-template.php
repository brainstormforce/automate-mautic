<script type="text/html" id="tmpl-bsfm-template">
	<# if ( 'condition-field' === data.clas ) { #>
		<span class="dashicons dashicons-minus remove-item"></span>
		<span class="dashicons dashicons-editor-justify sort-items"></span>
		<select class="select-condition form-control" name="pm_condition[]">
			<option> Select Condition </option>
			<option value="UR">User Register on WordPress</option>
			<option value="CP">User Post a Comment</option>
		</select>
		<div class="first-condition" style="display:inline;"></div>
		<div class="second-condition" style="display:inline;"></div>
	<# } #>
	<# if ( 'action-field' === data.clas ) { #>
		<span class="dashicons dashicons-minus remove-item"></span>
		<span class="dashicons dashicons-editor-justify sort-items"></span>
		<input type="hidden" name="pm_action[]" value="segment">
		<div class="first-action" style="display:inline;">
			<select id="sub-cp-action" class="sub-cp-action form-control" name="sub_seg_action[]">
				<option value="add_segment">Add to segment</option>
				<option value="remove_segment">Remove from segment</option>
			</select>
		</div>
		<div class="second-action" style="display:inline;">
			<?php Bsfm_Postmeta::select_all_segments(); ?>
		</div>
	<# } #>
	<# if( 'sub-cp-condition' === data.clas ) { #>
		<select id="sub-cp-condition" class="sub-cp-condition form-control" name="sub_cp_condition[]">
			<option value="ao_website">Anywhere On Website</option>
			<option value="os_page">On Specific Page</option>
			<option value="os_post">On Specific Post</option>
		</select>
	<# } #>
	<# if( 'os_page' === data.clas ) { #>
		<?php Bsfm_Postmeta::select_all_pages(); ?>
	<# } #>
	<# if( 'os_post' === data.clas ) { #>
		<?php Bsfm_Postmeta::select_all_posts(); ?>
	<# } #>
</script>