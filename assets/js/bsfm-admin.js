jQuery(document).ready(function( $ ) {
	var jq = jQuery.noConflict();
	jq( "#bsfm-sortable-condition" ).sortable();
	jq( "#bsfm-sortable-condition" ).disableSelection();
	jq( "#bsfm-sortable-action" ).sortable();
	jq( "#bsfm-sortable-action" ).disableSelection();
	jq( ".select-condition" ).select2();
	jq( ".select-action" ).select2();
	jq( ".sub-cp-action" ).select2();
	jq( ".root-seg-action" ).select2();
	jq( ".ss-cp-condition" ).select2();
	jq( ".sub-edd-condition" ).select2();
	jq( ".root-edd-condition" ).select2();
	jq( ".sub-cp-condition" ).select2();
	jq( ".edd_var_price" ).select2();
	jq( ".cf7_form" ).select2();
	jq( ".mautic_forms" ).select2();
	jq( ".sub-cf-condition" ).select2();
	jq( ".root-cp-condition" ).select2();

	//get markups from template
	var mbTemplate = wp.template( "bsfm-template" );
	jq(document).on( "click", ".remove-item", function() {
		var LastChild = jq(this).parent().hasClass('ui-state-default');
		if(!LastChild) {
			jq(this).parent().remove();
		}
	});
	jq(document).on( "click", ".bsfm-add-condition", function() {
		var conditionField = mbTemplate( { clas: "condition-field" } );
		var n = jq( "#bsfm-sortable-condition fieldset" ).length;
		n++;
		jq( "#bsfm-sortable-condition" ).append('<fieldset class="ui-state-new" id="item-'+ n +'">'+ conditionField +'</fieldset>');
		jq( ".select-condition" ).select2();
	});
	jq(document).on( "click", ".bsfm-add-action", function() {
		var actionField = mbTemplate( { clas: "action-field" } );
		var m = jq( "#bsfm-sortable-action fieldset" ).length;
		m++;
		jq( "#bsfm-sortable-action" ).append('<fieldset class="ui-state-new" id="item-'+ m +'">'+ actionField + '</fieldset>');
		jq( ".select-action" ).select2();
		jq( ".select-action" ).select2();
		jq( ".root-seg-action" ).select2();
		jq( ".sub-cp-action" ).select2();
	});
	jq(document).on( "change", ".select-condition", function() {
		parent = jq(this).parent();
		switch(this.value) {
			case 'CP' :
				var SelCondition = mbTemplate( { clas: "sub-cp-condition" } );
				parent.find('div.second-condition').html('');
				parent.find('div.first-condition').html(SelCondition);
				jq( ".sub-cp-condition" ).select2();
			break;

			case 'UR' :
				parent.find('div.first-condition').html('');
				parent.find('div.second-condition').html('');
			break;

			case 'CF7' :
				var cfSelect = mbTemplate( { clas: "select-cf" } );
				parent.find('div.second-condition').html('');
				parent.find('div.first-condition').html(cfSelect);
				jq( ".sub-cf-condition" ).select2();
			break;

			case 'EDD' :
				var cfSelect = mbTemplate( { clas: "select-edd-products" } );
				parent.find('div.second-condition').html('');
				parent.find('div.first-condition').html(cfSelect);
				jq( ".sub-edd-condition" ).select2();
			break;
		}
	});
	jq(document).on( "change", ".sub-cp-condition", function() {
		gParent = jq(this).parent().parent();
		switch(this.value) {
			case 'os_page' :
				var osPage = mbTemplate( { clas: this.value } );
				gParent.find('div.second-condition').html(osPage);
				jq( ".root-cp-condition" ).select2();
			break;
			
			case 'os_post' :
				var osPost = mbTemplate( { clas: this.value } );
				gParent.find('div.second-condition').html(osPost);
				jq( ".root-cp-condition" ).select2();
			break;

			case 'ao_website' :
				gParent.find('div.second-condition').html('');
		}
	});
	jq(document).on( "change", ".select-action", function() {
		parent = jq(this).parent();
		gParent = jq(this).parent().parent();
		switch(this.value) {
			case 'segment' :
				var SelAction = mbTemplate( { clas: "sub-seg-action" } );
				parent.find('div.first-action').html(SelAction);
				jq( ".sub-cp-action" ).select2();
				parent.find('div.second-action').html('');
			break;

			case 'tag' :
				parent.find('div.first-action').html('');
				parent.find('div.second-action').html('');
			break;
		}
	});
	// append form field mapping
	jq(document).on( "change", ".sub-cf-condition", function() {
		gParent = jq(this).parent().parent();
		cf7Id = parseInt(this.value);
		var data= {
			action:'get_cf7_fields',
			dataType: 'JSON',
			cf7Id: cf7Id
		};
		jq.post(ajaxurl, data, function(cf7) {
			cf7 = JSON.parse(cf7);
			var Mauticfields = mbTemplate( { clas: 'mautic_fields', fieldCnt: cf7.fieldCount, formId: cf7Id } );
			gParent.find('div.second-condition').html(Mauticfields);
			gParent.find('div.second-condition').append(cf7.selHtml);
			jq( ".cf7_form" ).select2();
			jq( ".mautic_forms" ).select2();
		});
	});
	// clean transients
	jq(document).on( "click", "#refresh-mautic", function() {
		jq( '.bsfm-wp-spinner' ).css( "visibility", "visible" );
		var data= {
			action:'clean_mautic_transient'
		};
		jq.post(ajaxurl, data, function(){
			jq( '.bsfm-wp-spinner' ).css( "visibility", "hidden" );
			jq( '.bsfm-wp-spinner-msg' ).css( "display", "inline-block" ).fadeOut(3000);
		});
	});
	jq(document).on( "change", ".sub-edd-condition", function() {
		gParent = jq(this).parent().parent();
		download_id = parseInt(this.value);
		var data= {
			action:'get_edd_var_price',
			download_id: download_id
		};
		jq.post(ajaxurl, data, function(selHtml) {
			var varPrices = mbTemplate( { clas: 'edd_payment_status' } );
			gParent.find('div.second-condition').html(varPrices);
			gParent.find('div.second-condition').append(selHtml);
			jq( ".root-edd-condition" ).select2();
			jq( ".edd_var_price" ).select2();
		});
	});
});