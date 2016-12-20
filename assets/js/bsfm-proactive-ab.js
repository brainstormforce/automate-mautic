jQuery(document).ready(function( $ ) {
	// Email must be an email
	jQuery( "#edd-email" ).focusout(function() {
		var input=jQuery(this);
		var re = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
		var is_email=re.test(input.val());
		if(is_email){
			var lead = jQuery(this).val();
			setTimeout(function(){
				var data= {
					action:'add_practive_leads',
					email: lead,
					ajaxurl: bsf_widget_notices.bsf_ajax_url
				};
				jQuery.post( bsf_widget_notices.bsf_ajax_url , data, function(selHtml) {
					console.log(selHtml);
				});
			},4000);
		}
	});
});