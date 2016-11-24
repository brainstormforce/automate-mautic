(function( $ ) {

	$(document).ready(function() {

		var btn_content		= 'fl-builder-add-content-button',
			btn_rows 		= 'fl-builder-add-ultimate-rows-button',
			btn_presets 	= 'fl-builder-add-ultimate-presets-button',
			btn_templates 	= 'fl-builder-add-ultimate-templates-button',

			panel_rows 		= 'fl-builder-panel-ultimate-rows',
			panel_presets 	= 'fl-builder-panel-ultimate-presets',
			panel_content 	= 'fl-builder-panel-content-rows';

		//	Add class to panel
		$( '.fl-builder-panel').not('.' + panel_rows).not('.' + panel_presets).addClass('fl-builder-panel-content-rows');

		// Focus Search
		$('#module_search').focus();

		//	Hide - Rows & Templates
		$( '.' + panel_rows ).hide();

		//	Content
		$( '.' + btn_content ).click(function(event) {
			$( '.' + panel_presets ).stop(true, true).animate({ right: '-350px' }, 0, function(){ $( '.' + panel_presets ).hide(); });
			$( '.' + panel_rows ).stop(true, true).animate({ right: '-350px' }, 0, function(){ $( '.' + panel_rows ).hide(); });
			$( '.' + panel_content ).stop(true, true).show().animate({ right: '0px' }, 0);
			$('#module_search').focus();
		});

		//	Rows
		$( '.' + btn_rows ).click(function(event) {
			$( '.' + panel_content ).stop(true, true).animate({ right: '-350px' }, 0, function(){ $( '.' + panel_content ).hide(); });
			$( '.' + panel_presets ).stop(true, true).animate({ right: '-350px' }, 0, function(){ $( '.' + panel_presets ).hide(); });
			$( '.' + panel_rows ).stop(true, true).show().animate({ right: '0' }, 0);
			$('#section_search').focus();
		});

		//	Presets
		$( '.' + btn_presets ).click(function(event) {
			$( '.' + panel_content ).stop(true, true).animate({ right: '-350px' }, 0, function(){ $( '.' + panel_content ).hide(); });
			$( '.' + panel_rows ).stop(true, true).animate({ right: '-350px' }, 0, function(){ $( '.' + panel_rows ).hide(); });
			$( '.' + panel_presets ).stop(true, true).show().animate({ right: '0' }, 0);
			$('#presets_search').focus();
		});

		/**
		 * Quick Search - Search by Title / Category Name
		 *
		 * Functionality works for BOTH (Module / Section) search...
		 */
		$('#module_search, #section_search, #presets_search').keyup(function(){

			var parent = $( this ).closest('.fl-builder-panel');
			var rex    = new RegExp( $(this).val(), 'i');
	        parent.find('.fl-builder-block').hide();
	        parent.find('.fl-builder-block > .fl-builder-block-title').filter(function () {
				var title  	   = $(this).html() || '',
					cat_name   = $(this).attr('data-cat-name') || '',
					tags_names = $(this).attr('data-tags') || '';
	        	return rex.test( cat_name + ' ' + title + ' ' + tags_names );
            }).parent('.fl-builder-block').show().closest('.fl-builder-blocks-section').addClass('fl-active');

            if( $.trim( $(this).val() ) == '' ) {
				parent.find('.fl-builder-blocks-section').removeClass('fl-active');            	
            }
	    });
	    $('.fl-builder-blocks-section').click(function(event) {
	    	$('#module_search, #section_search, #presets_search').val('');
	    	$( this ).find('.fl-builder-block').show();
	    });
	});

})( jQuery );
