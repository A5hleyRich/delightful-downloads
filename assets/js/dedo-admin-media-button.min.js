jQuery( document ).ready( function( $ ) {
	DEDO_Admin_Shortcode_Generator = {
		$cached: {
			download_dropdown: $( '#dedo-select-download-dropdown' ),
			style_dropdown: $( '#dedo-select-style-dropdown' ),
			button_dropdown: $( '#dedo-select-button-dropdown' ),
			button_container: $( '#dedo-button-dropdown-container' ),
			count_button: $( '#dedo-download-count' ),
			file_size_button: $( '#dedo-file-size' )
		},
		download: 0,
		init: function() {
			this.download = this.$cached.download_dropdown.val();
			this.enhanceNativeSelects();
			this.updateDownload();
			this.hideButtons();
			this.insert();
			this.download_count();
			this.file_size();
		},
		enhanceNativeSelects: function() {
			$( '#dedo-shortcode-modal select' ).addClass( 'dedo-native-select' );
		},
		updateDownload: function() {
			var self = this;
			this.$cached.download_dropdown.on( 'change', function() {
				self.download = $( this ).val();
			} );
		},
		hideButtons: function() {
			var self = this;
			this.$cached.style_dropdown.on( 'change', function() {
				if ( 'link' === $( this ).val() || 'plain_text' === $( this ).val() ) {
					self.$cached.button_container.hide();
					self.$cached.button_dropdown.val( '' );
				} else {
					self.$cached.button_container.show();
				}
			} ).trigger( 'change' );
		},
		insert: function() {
			var self = this;
			$( '#dedo-insert' ).on( 'click', function( e ) {
				var attrs = '';
				if ( '' !== self.$cached.style_dropdown.val() ) {
					attrs += ' style="' + self.$cached.style_dropdown.val() + '"';
				}
				if ( '' !== self.$cached.button_dropdown.val() ) {
					attrs += ' button="' + self.$cached.button_dropdown.val() + '"';
				}
				if ( $( '#dedo-custom-text' ).val().length > 0 ) {
					attrs += ' text="' + $( '#dedo-custom-text' ).val() + '"';
				}
				window.send_to_editor( '[ddownload id="' + self.download + '"' + attrs + ']' );
				$( 'body' ).trigger( 'closeModal' );
				e.preventDefault();
			} );
		},
		download_count: function() {
			var self = this;
			this.$cached.count_button.on( 'click', function( e ) {
				window.send_to_editor( '[ddownload_count id="' + self.download + '"]' );
				$( 'body' ).trigger( 'closeModal' );
				e.preventDefault();
			} );
		},
		file_size: function() {
			var self = this;
			this.$cached.file_size_button.on( 'click', function( e ) {
				window.send_to_editor( '[ddownload_filesize id="' + self.download + '"]' );
				$( 'body' ).trigger( 'closeModal' );
				e.preventDefault();
			} );
		}
	};
	DEDO_Admin_Shortcode_Generator.init();
} );
