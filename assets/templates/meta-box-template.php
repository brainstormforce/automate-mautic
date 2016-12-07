<script type="text/html" id="tmpl-bsfm-template">
	<# if( 'select-cf' === data.clas ) { #>
		<?php Bsfm_Postmeta::select_all_cf7forms(); ?>
	<# } #>
	<# if( 'select-edd-products' === data.clas ) { #>
		<?php Bsfm_Postmeta::select_all_edd_downloads(); ?>
	<# } #>
	<# if ( 'condition-field' === data.clas ) { #>
		<span class="dashicons dashicons-minus remove-item"></span>
		<span class="dashicons dashicons-editor-justify sort-items"></span>
		<select class="select-condition form-control" name="pm_condition[]">
			<option value="UR">User Register on WordPress</option>
			<option value="CP">User Post a Comment</option>
			<?php if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) { ?>
				<option value="CF7">User Submit Contact Form 7</option>
			<?php }
			if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) { ?>
				<option value="EDD">Easy Digital Downloads Purchase</option>
			<?php } ?>
		</select>
		<div class="first-condition" style="display:inline;"></div>
		<div class="second-condition" style="display:inline;"></div>
	<# } #>
	<# if ( 'action-field' === data.clas ) { #>
		<span class="dashicons dashicons-minus remove-item"></span>
		<span class="dashicons dashicons-editor-justify sort-items"></span>
		<select class="select-action form-control" name="pm_action[]">
	    	<option value="segment">Segment</option>
		</select>
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
			<option value="ao_website">Anywhere on website</option>
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
	<# if( 'mautic_fields' === data.clas ) { #>
		<table style="float: right;">
			<tbody>
			<# for (var i = 0; i < data.fieldCnt; i++) { #>
				<tr>
					<td>
						<select class="mautic_forms" name='mautic_cfields[<# print(data.formId); #>][]'>
							<?php Bsfm_Postmeta::mautic_get_all_cfields(); ?>
						</select>
					</td>
				</tr>
			<# } #>
			</tbody>
		</table>
	<# } #>
	<# if( 'edd_payment_status' === data.clas ) { #>
		<?php Bsfm_Postmeta::bsf_make_edd_payment_status(); ?>
	<# } #>
</script>