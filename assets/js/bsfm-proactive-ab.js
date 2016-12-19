jQuery(document).ready(function( $ ) {
		var jq = jQuery.noConflict();
		var temp = jQuery('#edd-email').val();
						//console.log(temp);
						var data= {
							action:'add_practive_leads',
							email: temp,
							ajaxurl: bsf_widget_notices.bsf_ajax_url
						};
			jQuery.post(ajaxurl, data, function(selHtml) {
					//console.log(selHtml);
		});
});

jQuery(document).ready(function( $ ) {
	// Email must be an email
	jQuery( "#edd-email" ).focusout(function() {
		var input=jQuery(this);
		var re = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
		var is_email=re.test(input.val());
		if(is_email){
			console.log('valid');
			input.removeClass("invalid").addClass("valid");
				setTimeout(function(){
				 	alert("API CALL");
				},4000);
		}
	});
});