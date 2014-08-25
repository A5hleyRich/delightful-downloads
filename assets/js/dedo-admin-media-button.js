jQuery( document ).ready( function( $ ) {

	DEDO_Admin_Shortcode_Generator = {

		download: 0,

		init: function() {
			// Set download
			this.download = $( '#dedo-select-download-dropdown' ).val();

			// Chosen dropdowns
			$( '#dedo-select-download-dropdown' ).chosen( { disable_search_threshold: 10, width: "100%" } );
			$( '#dedo-select-style-dropdown' ).chosen( { disable_search_threshold: 10, width: "100%" } );
			$( '#dedo-select-button-dropdown' ).chosen( { disable_search_threshold: 10, width: "100%" } );

			// Modules
			this.updateDownload();
			this.hideButtons();
			this.insert();
			this.download_count();
			this.file_size();	
		},

		// Update download on dropdown change
		updateDownload: function() {
			var self = this;

			$( '#dedo-select-download-dropdown' ).on( 'change', function() {
				self.download = $( this ).val();
			} );
		},

		// Hide buttons dropdown
		hideButtons: function() {
			$( '#dedo-select-style-dropdown' ).on( 'change', function() {
				if ( 'link' == $( this ).val() || 'plain_text' == $( this ).val() ) {
					$( '#dedo-button-dropdown-container' ).hide();
				}
				else {
					$( '#dedo-button-dropdown-container' ).show();
				}
			} );
		},

		// Insert button
		insert: function() {
			var self = this;			

			$( '#dedo-insert' ).on( 'click', function( e ) {
				
				var attrs = '';

				// Add style
				if ( '' !== $( '#dedo-select-style-dropdown' ).val() ) {
					attrs += ' style="' + $( '#dedo-select-style-dropdown' ).val() + '"';
				}

				// Add button
				if ( '' !== $( '#dedo-select-button-dropdown' ).val() ) {
					attrs += ' button="' + $( '#dedo-select-button-dropdown' ).val() + '"';
				}

				// Add text
				if ( $( '#dedo-custom-text' ).val().length > 0 ) {
					attrs += ' text="' + $( '#dedo-custom-text' ).val() + '"';
				}

				window.send_to_editor( '[ddownload id="' + self.download + '"' + attrs + ']' );
				$( 'body' ).trigger( 'closeModal' );

				e.preventDefault();
			} );
		},

		// Download count button
		download_count: function() {
			var self = this;			

			$( '#dedo-download-count' ).on( 'click', function( e ) {
				window.send_to_editor( '[ddownload_count id="' + self.download + '"]' );
				$( 'body' ).trigger( 'closeModal' );

				e.preventDefault();
			} );
		},

		// File size button
		file_size: function() {
			var self = this;			

			$( '#dedo-file-size' ).on( 'click', function( e ) {
				window.send_to_editor( '[ddownload_filesize id="' + self.download + '"]' );
				$( 'body' ).trigger( 'closeModal' );

				e.preventDefault();
			} );
		}
	}

	DEDO_Admin_Shortcode_Generator.init();

} );