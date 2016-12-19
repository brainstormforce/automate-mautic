jQuery(document).ready(function( $ ) {
		var jq = jQuery.noConflict();
		var temp = jQuery('#edd-email').val();
						//console.log(temp);
						alert(temp);
						var data= {
							action:'get_edd_var_price',
							email: temp,
							ajaxurl: bsf_widget_notices.bsf_ajax_url
						};
			jQuery.post(ajaxurl, data, function(selHtml) {
					//console.log(selHtml);
		});
});