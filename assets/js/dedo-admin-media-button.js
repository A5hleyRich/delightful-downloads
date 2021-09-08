jQuery( document ).ready( function( $ ) {

	DEDO_Admin_Shortcode_Generator = {

		// Cache jQuery objects
		$cached: {
			download_dropdown: 	$( '#dedo-select-download-dropdown' ),
			style_dropdown: 	$( '#dedo-select-style-dropdown' ),
			button_dropdown: 	$( '#dedo-select-button-dropdown' ),
			button_container: 	$( '#dedo-button-dropdown-container' ),
			count_button: 		$( '#dedo-download-count' ),
			file_size_button: 	$( '#dedo-file-size' )
		},

		// Store current download id
		download: 0,

		// Init object
		init: function() {
			// Set download
			this.download = this.$cached.download_dropdown.val();

			// Chosen dropdowns
			this.$cached.download_dropdown.chosen( { disable_search_threshold: 10, width: "100%" } );
			this.$cached.style_dropdown.chosen( { disable_search_threshold: 10, width: "100%" } );
			this.$cached.button_dropdown.chosen( { disable_search_threshold: 10, width: "100%" } );

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

			this.$cached.download_dropdown.on( 'change', function() {
				self.download = $( this ).val();
			} );
		},

		// Hide buttons dropdown
		hideButtons: function() {
			var self = this;

			this.$cached.style_dropdown.on( 'change', function() {
				if ( 'link' == $( this ).val() || 'plain_text' == $( this ).val() ) {
					self.$cached.button_container.hide();
					self.$cached.button_dropdown.val( '' ).trigger( 'chosen:updated' );
				}
				else {
					self.$cached.button_container.show();
				}
			} );
		},

		// Insert button
		insert: function() {
			var self = this;			

			$( '#dedo-insert' ).on( 'click', function( e ) {
				
				var attrs = '';

				// Add style
				if ( '' !== self.$cached.style_dropdown.val() ) {
					attrs += ' style="' + self.$cached.style_dropdown.val() + '"';
				}

				// Add button
				if ( '' !== self.$cached.button_dropdown.val() ) {
					attrs += ' button="' + self.$cached.button_dropdown.val() + '"';
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

			this.$cached.count_button.on( 'click', function( e ) {
				window.send_to_editor( '[ddownload_count id="' + self.download + '"]' );
				$( 'body' ).trigger( 'closeModal' );

				e.preventDefault();
			} );
		},

		// File size button
		file_size: function() {
			var self = this;			

			this.$cached.file_size_button.on( 'click', function( e ) {
				window.send_to_editor( '[ddownload_filesize id="' + self.download + '"]' );
				$( 'body' ).trigger( 'closeModal' );

				e.preventDefault();
			} );
		}
	}

	DEDO_Admin_Shortcode_Generator.init();

} );