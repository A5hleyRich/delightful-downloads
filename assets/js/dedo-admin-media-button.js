jQuery( document ).ready( function( $ ){

	// Modal window functionality	
	(function() {
		// Display download modal
		$( '#dedo-media-button' ).on( 'click', function( e ) {
			$( '#dedo-download-modal' ).show();
			
			e.preventDefault();
		} );
		
		// Close download modal
		$( '#dedo-download-modal-close' ).on( 'click', function( e ) {
			$( '#dedo-download-modal' ).hide();
			
			e.preventDefault();
		} );
		
		$( '.media-modal-backdrop' ).on( 'click', function( e ) {
			$( '#dedo-download-modal' ).hide();

			e.preventDefault();
		} );
	})();

	// Search functionality
	(function() {
		// Search list
		$( '#dedo-download-search' ).on( 'keyup', function() {
			var searchField = $( this ).val();

			// Loop through download items
			$( '.dedo-download-list tbody tr' ).each( function() {
				if( $( this ).children( '.title_column' ).text().search( new RegExp( searchField, 'i' ) ) < 0 ) {
					$( this ).fadeOut( 'fast' );
				}
				else {
					$( this ).fadeIn( 'fast' );
				}
			} );
		} );

		// Show downloads if clear button is pressed
		$( '#dedo-download-search' ).on( 'search', function() {
			$( '.dedo-download-list tbody' ).children().fadeIn( 'fast' );
		} );
	})();

	// Selectable table rows and details pane
	(function() {
		var download_id, download_title, download_count, download_size;

		var selectableOpts = {
				selected: function( e, ui ) {
					var selected = $( ui.selected );
						
						download_title = selected.children( '.title_column' ).text();
						download_size = selected.data( 'size' );
						download_count = selected.data( 'count' );
						download_id = selected.data( 'id' );

					$( '.download-details .meta .title strong' ).html( download_title );
					$( '.download-details .meta .size' ).html( download_size );
					$( '.download-details .meta .count span' ).html( download_count );					
					
					$( '.download-details' ).fadeIn( 300 );
				}
			};

		$( '.dedo-download-list tbody' ).selectable( selectableOpts );

		// Download insert button
		$( '#dedo-download-button' ).on( 'click', function( e ) {
			var download_text = $( '#dedo-download-text' ).val(),
				download_style = $( '#dedo-download-style' ).val(),
				download_button = $( '#dedo-download-color' ).val();

			var attrs = '';

			// Add title
			if( download_text.length > 0  ) {
				attrs = ' text="' + download_text + '"';
			}
			
			// Add style
			if( download_style != 'dedo_default' ) {
				attrs = attrs + ' style="' + download_style + '"';
			}

			// Add button style
			if( download_button != 'dedo_default' ) {
				attrs = attrs + ' button="' + download_button + '"';
			}
			
			// Send to editor
			window.send_to_editor( '[ddownload id="' + download_id + '"' + attrs +']' );
			e.preventDefault();
			
			// Hide modal
			$( '#dedo-download-modal' ).hide();
		} );

		// Filesize button
		$( '#dedo-filesize-button' ).on( 'click', function( e ) {
			window.send_to_editor( '[ddownload_size id="' + download_id + '"]' );
			e.preventDefault();
			
			$( '#dedo-download-modal' ).hide();
		} );

		// File count button
		$( '#dedo-count-button' ).on( 'click', function( e ) {
			window.send_to_editor( '[ddownload_count id="' + download_id + '"]' );
			e.preventDefault();
			
			$( '#dedo-download-modal' ).hide();
		} );

		// Hide/show color select on change
		$( '#dedo-download-style' ).on( 'change', function() {
			if( $( '#dedo-download-style' ).val() == 'button' ) {
				$( '.dedo-download-color-container' ).slideDown();
			}
			else {
				$( '.dedo-download-color-container' ).slideUp();	
			}
		} );

	})();

} );