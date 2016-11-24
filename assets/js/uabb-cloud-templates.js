jQuery( function( $ ) {

	/**
	 * AJAX Request Queue
	 * 
	 * - add()
	 * - remove()
	 * - run()
	 * - stop()
	 *
	 * @since 1.2.0.8
	 */
	var UABBajaxQueue = (function() {

		var requests = [];

		return {

			/**
			 * Add AJAX request
			 * 
			 * @since 1.2.0.8
			 */
			add:  function(opt) {
			    requests.push(opt);
			},

			/**
			 * Remove AJAX request
			 *
			 * @since 1.2.0.8
			 */
			remove:  function(opt) {
			    if( jQuery.inArray(opt, requests) > -1 )
			        requests.splice($.inArray(opt, requests), 1);
			},

			/**
			 * Run / Process AJAX request
			 *
			 * @since 1.2.0.8
			 */
			run: function() {
			    var self = this,
			        oriSuc;

			    if( requests.length ) {
			        oriSuc = requests[0].complete;

			        requests[0].complete = function() {
			             if( typeof(oriSuc) === 'function' ) oriSuc();
			             requests.shift();
			             self.run.apply(self, []);
			        };   

			        jQuery.ajax(requests[0]);

			    } else {

			      self.tid = setTimeout(function() {
			         self.run.apply(self, []);
			      }, 1000);
			    }
			},

			/**
			 * Stop AJAX request
			 *
			 * @since 1.2.0.8
			 */
			stop:  function() {

			    requests = [];
			    clearTimeout(this.tid);
			}
		};

	}());

	jQuery(document).ready(function($) {
		
		/**
		 *	Lazy Load
		 */
		jQuery(".uabb-template-screenshot img").lazyload({
		    effect : "fadeIn",
		    event : "sporty"
		});
		jQuery(window).bind("load", function() {
		    var timeout = setTimeout(function() {
		        jQuery(".uabb-template-screenshot img").trigger("sporty")
		    }, 1000);
		});

		/**
		 *	Shuffle JS
		 */
		var grid = jQuery('.uabb-templates-page-templates');

		grid.shuffle({
			itemSelector: '.uabb-single-page-templates',
		});

		// ReShuffle - When user clicks a filter item
		jQuery('.uabb-templates-filter a').click(function (e) {
			e.preventDefault();

			// set active class
			jQuery('.uabb-templates-filter a').removeClass('active');
			jQuery(this).addClass('active');

			// get group name from clicked item
			var groupName = jQuery(this).attr('data-group');

			// reshuffle grid
			grid.shuffle('shuffle', groupName );

		});

		// ReShuffle - When user clicks a tab
		jQuery('body').on('click', '.fl-settings-nav li a', function (event) {
			var hash = jQuery(this).attr('href') || '';
			if( hash == '#uabb-cloud-templates' ) {
				jQuery( window ).trigger( "resize.shuffle" );
			}
		});

	});

	/**
	 * Templates Preview
	 */
	jQuery('body').on('click', '.uabb-template-screenshot', function (event) {

		var template_preview_url = jQuery(this).attr( 'data-preview-url' ) || '',
			template_name        = jQuery(this).attr( 'data-template-name' ) || '',
			is_downlaoded        = jQuery(this).parents( '.uabb-template-block' ).attr('data-is-downloaded') || '',
			template_id          = jQuery(this).attr( 'data-template-id' ),
			template_type        = jQuery(this).attr( 'data-template-type' ),
			template_dat_url     = jQuery(this).attr( 'data-template-dat-url' );

		if( '' != template_preview_url ) {

			/**
			 * Thickbox options
			 */
			template_preview_url = template_preview_url + '?TB_iframe=true'; // Required

			/**
			 * Open ThickBox
			 */
			tb_show( template_name , template_preview_url );

			// jQuery('#TB_title').addClass('UABB_TB_title');
			jQuery('#TB_window').addClass('UABB_TB_window');
			jQuery("#TB_window").append("<div class='UABB_TB_loader spinner is-active'></div>");
			jQuery('#TB_iframeContent').addClass('UABB_TB_iframeContent');

			/**
			 * Add Download Button
			 */
			if( is_downlaoded != 'true' ) {

				var output   = '<span class="button button-primary button-small uabb-cloud-process" data-operation="download">'
							 + '<i class="dashicons dashicons-update"></i>'
							 + '<span class="msg"> '+UABBCloudTemplates.btnTextDownload+' </span>'
							 + '<input type="hidden" class="template-dat-meta-id" value="'+ template_id +'" />'
							 + '<input type="hidden" class="template-dat-meta-type" value="'+ template_type +'" />'
							 + '<input type="hidden" class="template-dat-meta-dat_url" value="'+ template_dat_url +'" />';
							 + '</span>';

				jQuery('#TB_title').append( output );

				/**
				 * Popup download template process
				 */
				jQuery( window ).on( 'uabb-template-downloaded', function( template_dat_url_local ) {
					jQuery('#TB_title .uabb-cloud-process').remove();
					var output  = '<span class="uabb-cloud-process">'
								 + '<i class="dashicons dashicons-yes"></i>'
								 + '<span class="msg"> '+UABBCloudTemplates.btnTextInstall+' </span>'
								 + '</span>';
					jQuery('#TB_title').append( output );

					jQuery('#' + template_id ).addClass( 'uabb-downloaded' );
					jQuery('#' + template_id ).attr('data-is-downloaded', true );
					jQuery('#' + template_id ).attr('data-groups', '' );


					var output   = '<span class="button button-primary uabb-cloud-process" data-operation="remove">'
								 + '	<i class="dashicons dashicons-no" style="padding: 3px;"></i>'
								 + '	<span class="msg"> '+UABBCloudTemplates.btnTextRemove+' </span>'
								 + '	<input type="hidden" class="template-dat-meta-id" value="'+ template_id +'" />'
								 + '	<input type="hidden" class="template-dat-meta-type" value="'+ template_type +'" />'
								 + '	<input type="hidden" class="template-dat-meta-dat_url_local" value="'+ template_dat_url_local +'" />'
								 + '</span>'
								 + '<span class="button button-sucess uabb-installed-btn">'
								 + '	<i class="dashicons dashicons-yes" style="padding: 3px;"></i>'
								 + '	<span class="msg">'+UABBCloudTemplates.btnTextInstall+'</span>'
								 + '</span>';

					jQuery('#' + template_id ).find('.uabb-template-actions').html( output );

				});
			} else {

				var output  = '<span class="uabb-cloud-process installed"><i class="dashicons dashicons-yes"></i>'
							+ '<span class="msg"> '+UABBCloudTemplates.btnTextInstall+' </span></span>';
				jQuery('#TB_title').append( output );
			}
	
			//	hide iframe until complete load and show loader
			//	once complete iframe loaded then disable loader
			jQuery('#TB_iframeContent').hide();
			jQuery('#TB_iframeContent').bind('load', function(){
		        jQuery("#TB_window").find(".spinner").remove();
		        jQuery('#TB_iframeContent').show();
		    });
		}
	});


	/**
	 *	Template Tabs
	 */
	jQuery("#uabb-cloud-templates-tabs").tabs();

	/**
	 * Templates Count
	 */
	jQuery('body').on('click', '.uabb-filter-links a', function (event) {
		var count = jQuery(this).attr( 'data-count' ) || 0;
		jQuery('.filter-count .count').html( count );

		//	Reshuffle
		// jQuery( window ).trigger( "resize.shuffle" );
	});

	/**
	 * Process of cloud templates - (download, remove & fetch)
	 */
	UABBajaxQueue.run();

	jQuery('body').on('click', '.uabb-cloud-process', function (event) {
		event.preventDefault();

		var btn             	= jQuery(this),
			meta_id             = btn.find('.template-dat-meta-id').val() || '',
			meta_type           = btn.find('.template-dat-meta-type').val() || '',
			btn_template        = btn.parents('.uabb-template-block'),
			btn_template_image  = btn_template.find('.uabb-template-screenshot');
			btn_template_groups = btn_template.attr( 'data-groups' ) || '',
			btn_operation       = btn.attr('data-operation') || '',
			errorMessage        = UABBCloudTemplates.errorMessage,
			successMessage      = UABBCloudTemplates.successMessage,
			processAJAX         = true;

		//	add processing class
		if( meta_id != 'undefined' ) {
			$('#' + meta_id ).addClass('uabb-template-processing');
		}

		//	remove error message if exist
		if( btn_template_image.find('.notice').length ) {
			btn_template_image.find('.notice').remove();
		}

		if( '' != btn_operation ) {

			btn.find('i').addClass('uabb-reloading-iconfonts');

			switch( btn_operation ) {
				case 'fetch':
									jQuery('.wp-filter').find('.uabb-cloud-process i').addClass('uabb-reloading-iconfonts');
									btn.parents('.uabb-cloud-templates-not-found').find('.uabb-cloud-process i').show();
									var dataAJAX 		=  	{
																action: 'uabb_cloud_dat_file_fetch',
															};

					break;

				case 'download':
									var meta_dat_url   = btn.find('.template-dat-meta-dat_url').val() || '',
										successMessage = UABBCloudTemplates.successMessageDownload,
										dataAJAX       = {
															action: 'uabb_cloud_dat_file',
															dat_file: meta_dat_url,
															dat_file_id: meta_id,
															// dat_file_name: meta_name,
															// dat_file_image: meta_image,
															dat_file_type: meta_type,
															// dat_file_dat_url: meta_dat_url,
														};

										if( meta_dat_url === '' ) {
											processAJAX = false;
										}
					break;

				case 'remove':
								var meta_url_local = btn.find('.template-dat-meta-dat_url_local').val() || '',
									successMessage = UABBCloudTemplates.successMessageRemove,
									dataAJAX       = {
														action: 'uabb_cloud_dat_file_remove',
														dat_file_id: meta_id,
														dat_file_type: meta_type,
														dat_file_url_local: meta_url_local,
													};

									if( meta_id === '' ) {
										processAJAX = false;
									}
					break;
			}
			
			if( processAJAX ) {

		       	UABBajaxQueue.add({
					url: UABBCloudTemplates.ajaxurl,
					type: 'POST',
					data: dataAJAX,
					success: function(data){

						/**
						 * Parse response
						 */
						data = JSON.parse( data );
						console.log('data: ' + JSON.stringify( data ) );

						var status                 = ( data.hasOwnProperty('status') ) ? data['status'] : '';
						var msg                    = ( data.hasOwnProperty('msg') ) ? data['msg'] : '';
						var template_id            = ( data.hasOwnProperty('id') ) ? data['id'] : '';
						var template_type          = ( data.hasOwnProperty('type') ) ? data['type'] : '';
						var template_dat_url       = ( data.hasOwnProperty('dat_url') ) ? decodeURIComponent( data['dat_url'] ) : '';
						var template_dat_url_local = ( data.hasOwnProperty('dat_url_local') ) ? decodeURIComponent( data['dat_url_local'] ) : '';

						if( status == 'success' ) {

							//	remove processing class
							if( meta_id != 'undefined' ) {
								$('#' + meta_id ).removeClass('uabb-template-processing');
							}

							switch( btn_operation ) {
								case 'remove':
													jQuery( window ).trigger( 'uabb-template-removed' );

													btn.removeClass('button-secondary');
													btn.addClass('button-primary');
													btn.find('i').removeClass('uabb-reloading-iconfonts dashicons-no dashicons-update');
													btn.find('i').addClass('dashicons-yes');

													btn.find('.msg').html( UABBCloudTemplates.successMessageRemove );
													setTimeout(function() {

														btn_template.attr('data-is-downloaded', '');
														btn_template.removeClass( 'uabb-downloaded' );
														btn.attr('data-operation', 'download');

														var output   = '<i class="dashicons dashicons-update" style="padding: 3px;"></i>'
																	 + '<span class="msg"> '+UABBCloudTemplates.btnTextDownload+' </span>'
																	 + '<input type="hidden" class="template-dat-meta-id" value="'+ template_id +'" />'
																	 + '<input type="hidden" class="template-dat-meta-type" value="'+ template_type +'" />'
																	 + '<input type="hidden" class="template-dat-meta-dat_url" value="'+ template_dat_url +'" />';

														btn.html( output );
														btn.parents('.uabb-template-actions').find('.uabb-installed-btn').remove();

													}, 1000);

									break;
								case 'download':
													jQuery( window ).trigger( 'uabb-template-downloaded', template_dat_url_local );

													btn.removeClass('button-secondary');
													btn.addClass('button-primary');
													btn.find('i').removeClass('uabb-reloading-iconfonts dashicons-no dashicons-update');
													btn.find('i').addClass('dashicons-yes');

													btn.find('.msg').html( UABBCloudTemplates.successMessageDownload );
													setTimeout(function() {
		
														btn_template.attr('data-is-downloaded', 'true');
														btn_template.addClass( 'uabb-downloaded' );
														btn.attr('data-operation', 'remove');

														var output   = '<i class="dashicons dashicons-no" style="padding: 3px;"></i>'
																	 + '<span class="msg"> '+UABBCloudTemplates.btnTextRemove+' </span>'
																	 + '<input type="hidden" class="template-dat-meta-id" value="'+ template_id +'" />'
																	 + '<input type="hidden" class="template-dat-meta-type" value="'+ template_type +'" />'
																	 + '<input type="hidden" class="template-dat-meta-dat_url_local" value="'+ template_dat_url_local +'" />';

														var outputInstalled = '<span class="button button-sucess uabb-installed-btn">'
																			+ '<i class="dashicons dashicons-yes" style="padding: 3px;"></i>'
																			+ '<span class="msg">'+UABBCloudTemplates.btnTextInstall+'</span>'
																			+ '</span>';

														btn.html( output );
														btn.parents('.uabb-template-actions').append( outputInstalled );

													}, 1000);

													return;
									break;

								case 'fetch':
													jQuery( window ).trigger( 'uabb-template-fetched' );

													btn.parents('.wp-filter').find('.uabb-cloud-process').removeClass('button-secondary');
													btn.parents('.wp-filter').find('.uabb-cloud-process').addClass('button-primary');
													btn.parents('.wp-filter').find('.uabb-cloud-process i').removeClass('uabb-reloading-iconfonts dashicons-no dashicons-update');
													btn.parents('.wp-filter').find('.uabb-cloud-process i').addClass('dashicons-yes');

													btn.parents('.wp-filter').find('.uabb-cloud-process .msg').html( UABBCloudTemplates.successMessageFetch );
													location.reload();

									break;
							}

						} else {

							/**
							 * Something went wrong
							 */
							if( '' != msg ) {
	
								btn.find('.msg').html( UABBCloudTemplates.errorMessageTryAgain );
								btn.find('i').removeClass('uabb-reloading-iconfonts');

								var message = '<div class="notice notice-error uct-notice is-dismissible"><p>' + msg + '	</p></div>';
								btn_template_image.append( message );

							} else {
								btn.find('.msg').html( status );								
							}
						}
					}
				});
			}
		}

	});

} );
