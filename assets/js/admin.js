(function( $ ) {
    
    /**
     * JavaScript class for AutomatePlug.
     *
     * @since 0.0.1
     */

  	var mbTemplate = wp.template( 'apm-template' );

    var AutomatePlugMauticWP = {
        
        /**
         * Initializes the services logic.
         *
         * @return void
         * @since 1.2.0
         */
		init: function() {

			//get markups from template
			var $ = jQuery.noConflict();

			$( document ).on( 'click', '.remove-item', this._removeItem );
			$( document ).on( 'click', '.apm-add-condition', this._ApendCondition );
			$( document ).on( 'click', '.apm-add-action', this._AddAction );
			$( document ).on( 'change', '.select-condition', this._selectCondition );
			$( document ).on( 'change', '.sub-cp-condition', this._subCondition );
			$( document ).on( 'change', '.select-action', this._selectAction );
			$( document ).on( 'change', '.sub-seg-action', this._subAction );
			$( document ).on( 'click', '#refresh-mautic', this._refreshMautic );
			$( document ).on( 'click', '.ap-toogle-option', this._toggleMautic );
			$( document ).on( 'click', '.apm-disconnect', this._disconnectMautic );
			$( '.apm-config-form' ).on( 'click', '.save-amp-settings', this._saveSettings );
			$( '.apm-config-form' ).on( 'click', '.rule-delete-link', this._deleteRule );
			$( '#apm-sortable-condition' ).sortable();
			$( '#apm-sortable-condition' ).disableSelection();
			$( '#apm-sortable-action' ).sortable();
			$( '#apm-sortable-action' ).disableSelection();
			$('.apm-metabox').find('select').select2();
			$('#automate-config-form').find('select').select2();
		},

		_removeItem: function() {
			var LastChild = $(this).parent().hasClass('ui-state-default');
			if(!LastChild) {
			$(this).parent().remove();
			}
		},

		_ApendCondition: function() {
					var conditionField = mbTemplate( { clas: 'condition-field' } );
				var n = $( '#apm-sortable-condition fieldset' ).length;
				n++;
				$( '#apm-sortable-condition' ).append('<fieldset class="ui-state-new" id="item-'+ n +'">'+ conditionField +'</fieldset>');
				$( '.select-condition' ).select2();
		},

		_AddAction: function() {
				var actionField = mbTemplate( { clas: "action-field" } );
				var m = $( '#apm-sortable-action fieldset' ).length;
				m++;
				$( '#apm-sortable-action' ).append('<fieldset class="ui-state-new" id="item-'+ m +'">'+ actionField + '</fieldset>');
				$( '.select-action' ).select2();
				$( '.select-action' ).select2();
				$( '.root-seg-action' ).select2();
				$( '.sub-seg-action' ).select2();
		},

		_selectCondition: function() {
			parent = $(this).parent();
			switch(this.value) {
				case 'CP' :
				case 'CP_APPROVE' :
				
					var SelCondition = mbTemplate( { clas: "sub-cp-condition" } );
					parent.find('div.second-condition').html('');
					parent.find('div.first-condition').html(SelCondition);
					$( ".sub-cp-condition" ).select2();
				break;

				case 'UR' :
					parent.find('div.first-condition').html('');
					parent.find('div.second-condition').html('');
				break;

				case 'Default' :
					parent.find('div.first-condition').html('');
					parent.find('div.second-condition').html('');
			}
		},

		_subCondition: function() {
				gParent = $(this).parent().parent();
				switch(this.value) {
					case 'os_page' :
						var osPage = mbTemplate( { clas: this.value } );
						gParent.find('div.second-condition').html(osPage);
						$( '.root-cp-condition' ).select2();
					break;
					
					case 'os_post' :
						var osPost = mbTemplate( { clas: this.value } );
						gParent.find('div.second-condition').html(osPost);
						$( '.root-cp-condition' ).select2();
					break;

					case 'ao_website' :
						gParent.find('div.second-condition').html('');
				}
		},

		_selectAction: function() {
			parent = $(this).parent();
			gParent = $(this).parent().parent();
			switch(this.value) {
				case 'segment' :
					var SelAction = mbTemplate( { clas: 'sub-seg-action' } );
					parent.find('div.first-action').html(SelAction);
					$( '.sub-seg-action' ).select2();
					parent.find('div.second-action').html('');
				break;
			}
		},

		_subAction: function() {
			parent = $(this).parent();
			gParent = $(this).parent().parent();

			switch(this.value) {
				case 'add_tag' :
					gParent.find('div.second-action').html('');
					var SelAction = mbTemplate( { clas: 'sub-tag-action' } );
					gParent.find('div.second-action').html(SelAction);
				break;
				default:
					var SelAction = mbTemplate( { clas: 'get-all-segments' } );
					gParent.find('div.second-action').html(SelAction);
					$( ".root-seg-action" ).select2();
				break;
			}
		},

		_refreshMautic: function() {
			$( '.apm-wp-spinner' ).css( 'visibility', 'visible' );
			var data= {
				action:'clean_mautic_transient',
				nonce : ApmAdminScript.ajax_nonce
			};
			$.post(ajaxurl, data, function(){

				$( '.apm-wp-spinner' ).removeClass('spinner');
				$( '.apm-wp-spinner' ).addClass('dashicons dashicons-yes');
				location.reload();
			});
		},

		_disconnectMautic: function() {
			if( confirm('Are you sure you wish to disconnect from Mautic?') ) {
				var data= {
					action:'config_disconnect_mautic',
					nonce : ApmAdminScript.ajax_nonce
				};
				$.post(ajaxurl, data, function(selHtml) {
					location.reload();
				});
			}
			else {
				return false;
			}
		},

		_deleteRule: function() {
			if ( ! confirm( 'Are you sure you want to delete rule?' ) ) {
				return false;
			}
		},
		_saveSettings: function() {
			$( '.apm-wp-spinner' ).css( 'visibility', 'visible' );
		},

		_toggleMautic: function() {
			$( '.inside' ).toggleClass('hide');
		}
	};

	$( function() {
		AutomatePlugMauticWP.init();
	});

})( jQuery );