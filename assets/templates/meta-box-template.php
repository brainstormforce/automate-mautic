<script type="text/html" id="tmpl-bsfm-template">
	<# if( 'select-cf' === data.clas ) { #>
		<?php Bsfm_Postmeta::select_all_cf7forms(); ?>
	<# } #>
	<# if ( 'condition-field' === data.clas ) { #>
		<span class="dashicons dashicons-minus remove-item"></span>
		<span class="dashicons dashicons-editor-justify sort-items"></span>
		<select class="select-condition form-control" name="pm_condition[]">
			<option value="UR">User Register on WordPress</option>
			<option value="CP">User Post a Comment</option>
			<option value="CF7">User Submit Contact Form 7</option>
		</select>
		<div class="first-condition" style="display:inline;"></div>
		<div class="second-condition" style="display:inline;"></div>
	<# } #>
	<# if ( 'action-field' === data.clas ) { #>
		<span class="dashicons dashicons-minus remove-item"></span>
		<span class="dashicons dashicons-editor-justify sort-items"></span>
		<select class="select-action form-control" name="pm_action[]">
	  		<option value="tag">Add Tag</option>
	    	<option value="segment">Segment</option>
		</select>
		<div class="first-action" style="display:inline;"></div>
		<div class="second-action" style="display:inline;"></div>
	<# } #>
	<# if( 'sub-cp-condition' === data.clas ) { #>
		<select id="sub-cp-condition" class="sub-cp-condition form-control" name="sub_cp_condition[]">
			<option value="ao_website">Anywhere on website</option>
			<option value="os_page">On Specific Page</option>
			<option value="os_post">On Specific Post</option>
		</select>
	<# } #>
	<# if( 'sub-seg-action' === data.clas ) { #>
		<select id="sub-cp-action" class="sub-cp-action form-control" name="sub_seg_action[]">
			<option value="new_segments">Enter new segment</option>
			<option value="pre_segments">Select predefined segment</option>
		</select>
	<# } #>
	<# if( 'os_page' === data.clas ) { #>
		<?php Bsfm_Postmeta::select_all_pages(); ?>
	<# } #>
	<# if( 'os_post' === data.clas ) { #>
		<?php Bsfm_Postmeta::select_all_posts(); ?>
	<# } #>
	<# if( 'pre_segments' === data.clas ) { #>
		<?php Bsfm_Postmeta::select_all_segments(); ?>
	<# } #>
	<# if( 'new_segments' === data.clas ) { #>
		<input type="text" name="new_segment" placeholder="enter name of segment" />
	<# } #>
	<# if( 'sub-cf-condition' === data.clas ) { #>
		<div style='background: f1f1f1;height: 200px;'> 
			<p><# alert(data.cf7Id); #></p>
			<p><# alert(data.clas); #></p>
			<?php //Bsfm_Postmeta::get_all_cf7_fields(); ?>
		</div>
	<# } #>
	<# if( 'm_form' === data.clas ) { #>
		<?php Bsfm_Postmeta::select_all_mforms(); ?>
	<# } #>
</script>