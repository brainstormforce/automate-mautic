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
	jq( ".sub-cp-condition" ).select2();
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
	// clean transients
	jq(document).on( "click", "#refresh-mautic", function() {
		jq( '.bsfm-wp-spinner' ).css( "visibility", "visible" );
		var data= {
			action:'clean_mautic_transient'
		};
		jq.post(ajaxurl, data, function(){
			location.reload();
		});
	});

	jq('.bsfm-disconnect-mautic').click(function(){
		if( confirm('Are you sure you wish to disconnect from Mautic?') ) {
			var data= {
				action:'config_disconnect_mautic'
			};
			jq.post(ajaxurl, data, function(selHtml) {
				location.reload();
			});
		}
		else {
			return false;		
		}
	});
	jq( '.bsfm-config-bsfm-settings-form' ).on( 'click', '.rule-delete-link', function() {
		if ( ! confirm( "Are you sure you want to delete rule?" ) ) {
			return false;
		}
	});
});