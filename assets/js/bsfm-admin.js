jQuery(document).ready(function( $ ) {
	var jq = jQuery.noConflict();
    jq( "#bsfm-sortable-condition" ).sortable({
       	axis: 'y',
       	stop: function (event, ui) {
	        var data = $(this).sortable('serialize');
	        var res = data.split("&");
	        var res = res[0];
			//var res = res.slice(1,4);
			//console.log(res);
		}
    });
    jq( "#bsfm-sortable-condition" ).disableSelection();
  	jq( "#bsfm-sortable-action" ).sortable();
    jq( "#bsfm-sortable-action" ).disableSelection();
    jq( ".select-condition" ).select2();
    jq( ".select-action" ).select2();
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
	jq(document).on( "change", ".sub-cp-action", function() {
		gParent = jq(this).parent().parent();
		switch(this.value) {
			case 'pre_segments' :
				var PreSeg = mbTemplate( { clas: this.value } );
				gParent.find('div.second-action').html(PreSeg);
				jq( ".root-seg-action" ).select2();
			break;
			case 'new_segments' :
				var NewSeg = mbTemplate( { clas: this.value } );
				gParent.find('div.second-action').html(NewSeg);
			break;
		}
	});
	// apend form field mapping
	jq(document).on( "change", ".sub-cf-condition", function() {
		gParent = jq(this).parent().parent();
		cf7Id = parseInt(this.value);
		var cf7MapFields = mbTemplate( { clas: 'sub-cf-condition', cf7Id: cf7Id } );
		gParent.find('div.second-condition').html(cf7MapFields);
		var data={
			action:'get_cf7_fields',
			cf7Id: cf7Id
		};
		jq.post(ajaxurl, data, function(response) {
			alert('Got this from the server: ' + response);
		});
	});
	/**/
	//methods
	jq(document).on( "click", ".select-method input", function() {
		if( this.value == 'm_form' ) {
			var MauticForms = mbTemplate( { clas: this.value } );
			jq('.select-method').append(MauticForms);
		}
	});
});