jQuery(document).ready(function( $ ) {
	var jq = jQuery.noConflict();
	jq( "#ampw-sortable-condition" ).sortable();
	jq( "#ampw-sortable-condition" ).disableSelection();
	jq( "#ampw-sortable-action" ).sortable();
	jq( "#ampw-sortable-action" ).disableSelection();
	jq( ".select-condition" ).select2();
	jq( ".select-action" ).select2();
	jq( ".sub-seg-action" ).select2();
	jq( ".root-seg-action" ).select2();
	jq( ".ss-cp-condition" ).select2();
	jq( ".sub-cp-condition" ).select2();
	jq( ".root-cp-condition" ).select2();

	//get markups from template
	var mbTemplate = wp.template( "apm-template" );
	jq(document).on( "click", ".remove-item", function() {
		var LastChild = jq(this).parent().hasClass('ui-state-default');
		if(!LastChild) {
			jq(this).parent().remove();
		}
	});
	jq(document).on( "click", ".ampw-add-condition", function() {
		var conditionField = mbTemplate( { clas: "condition-field" } );
		var n = jq( "#ampw-sortable-condition fieldset" ).length;
		n++;
		jq( "#ampw-sortable-condition" ).append('<fieldset class="ui-state-new" id="item-'+ n +'">'+ conditionField +'</fieldset>');
		jq( ".select-condition" ).select2();
	});
	jq(document).on( "click", ".ampw-add-action", function() {
		var actionField = mbTemplate( { clas: "action-field" } );
		var m = jq( "#ampw-sortable-action fieldset" ).length;
		m++;
		jq( "#ampw-sortable-action" ).append('<fieldset class="ui-state-new" id="item-'+ m +'">'+ actionField + '</fieldset>');
		jq( ".select-action" ).select2();
		jq( ".select-action" ).select2();
		jq( ".root-seg-action" ).select2();
		jq( ".sub-seg-action" ).select2();
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
				jq( ".sub-seg-action" ).select2();
				parent.find('div.second-action').html('');
			break;
		}
	});

	jq(document).on( "change", ".sub-seg-action", function() {
		parent = jq(this).parent();
		gParent = jq(this).parent().parent();

		switch(this.value) {
			case 'add_tag' :
				gParent.find('div.second-action').html('');
				var SelAction = mbTemplate( { clas: "sub-tag-action" } );
				gParent.find('div.second-action').html(SelAction);
			break;
			default:
				var SelAction = mbTemplate( { clas: "get-all-segments" } );
				gParent.find('div.second-action').html(SelAction);
				jq( ".root-seg-action" ).select2();
			break;
		}
	});

	// clean transients
	jq(document).on( "click", "#refresh-mautic", function() {
		var data= {
			action:'clean_mautic_transient'
		};
		jq.post(ajaxurl, data, function(){
			location.reload();
		});
	});

	jq('.ampw-disconnect-mautic').click(function() {
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
	jq( '.ampw-config-settings-form' ).on( 'click', '.rule-delete-link', function() {
		if ( ! confirm( "Are you sure you want to delete rule?" ) ) {
			return false;
		}
	});

	jq('#save-amp-settings').click(function() {
		
		 jq( '.apm-wp-spinner' ).css( "visibility", "visible" );
	});

});