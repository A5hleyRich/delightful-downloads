jQuery( document ).ready( function( $ ) {

	// Main Add/Edit Download Screen
	DEDO_Admin_Download = {

		options: {},

		init: function( options ) {
			this.options = options;
			this.eventListeners();
			this.updateStatus();
		},

		eventListeners: function() {
			var self = this;

			$( '.dedo-delete-file' ).on( 'click', function( e ) {
				self.deleteFile();
				e.preventDefault();
			} );

			$( document ).on( 'change', '[name="members_only"]', function() {
				if ( '1' === $( this ).val() || '' === $( this ).val() ) {
					$( '#members_only_sub' ).show();
				} else {
					$( '#members_only_sub' ).hide();
				}
			} );
		},

		addFile: function( url ) {
			$( '#dedo-file-url' ).val( url );
			$( '.file-icon img' ).attr( 'src', this.options.default_icon );
			$( '.file-name' ).html( '--' );
			$( '.file-size' ).html( '--' );
			this.updateStatus();
			this.toggleViews();
		},

		deleteFile: function() {
			$( '#dedo-file-url' ).val( '' );
			this.updateStatus();
			this.toggleViews();
		},

		toggleViews: function() {
			if ( '' !== $( '#dedo-file-url' ).val() ) {
				$( '#dedo-new-download' ).hide( 0, function() {
					$( '#dedo-existing-download' ).show();
				} );
			} else {
				$( '#dedo-existing-download' ).hide( 0, function() {
					$( '#dedo-new-download' ).show();
				} );
			}
		},

		updateStatus: function() {
			var self = this;
			var url = $.trim( $( '#dedo-file-url' ).val() || '' );

			if ( '' === url ) {
				$( '.file-name' ).html( '--' );
				$( '.file-size' ).html( '--' );
				$( '.file-status .status' ).removeClass( 'spinner local remote warning' ).addClass( 'warning' ).attr( 'title', self.options.lang_warning );
				return;
			}

			$( '.file-status .status' ).removeClass( 'local remote warning' ).addClass( 'spinner' );

			$.ajax( {
				url: self.options.ajaxURL,
				type: 'POST',
				timeout: 15000,
				data: {
					action: self.options.action,
					nonce: self.options.nonce,
					url: url
				},
				dataType: 'json',
				success: function( response ) {
					if ( 'success' === response.status && response.content ) {
						$( '.file-name' ).html( response.content.filename || '--' );
						$( '.file-icon img' ).attr( 'src', response.content.icon );
						$( '.file-size' ).html( response.content.size );
						$( '.file-status .status' ).removeClass( 'spinner' ).addClass( response.content.type );

						if ( 'local' === response.content.type ) {
							$( '.file-status .status' ).attr( 'title', self.options.lang_local );
						}

						if ( 'remote' === response.content.type ) {
							$( '.file-status .status' ).attr( 'title', self.options.lang_remote );
						}
					} else {
						$( '.file-status .status' ).removeClass( 'spinner' ).addClass( 'warning' ).attr( 'title', self.options.lang_warning );
					}
				},
				error: function() {
					$( '.file-status .status' ).removeClass( 'spinner local remote' ).addClass( 'warning' ).attr( 'title', self.options.lang_warning );
				}
			} );
		}
	};

	DEDO_Upload_Modal = {
		$container: $( '#dedo-upload-modal #dedo-drag-drop-area' ),
		$progressPercent: $( '#dedo-progress-percent' ),
		$progressText: $( '#dedo-progress-text' ),
		$progressError: $( '#dedo-progress-error' ),
		uploader: {},

		init: function( options ) {
			this.options = options;
			this.uploader = new plupload.Uploader( this.options );
			this.uploader.init();
			this.uploadListeners();
		},

		uploadListeners: function() {
			var self = this;

			this.uploader.bind( 'FilesAdded', function( up ) {
				self.$container.addClass( 'uploading' );
				self.$progressError.hide();
				up.refresh();
				up.start();
			} );

			this.uploader.bind( 'UploadProgress', function( up, file ) {
				self.$progressPercent.css( 'width', file.percent + '%' );
				self.$progressText.html( file.percent + '%' );
			} );

			this.uploader.bind( 'FileUploaded', function( up, file, response ) {
				var parsed = $.parseJSON( response.response );

				if ( parsed.error && parsed.error.code ) {
					self.uploader.trigger( 'Error', {
						code: parsed.error.code,
						message: parsed.error.message,
						file: file
					} );
				} else {
					DEDO_Admin_Download.addFile( parsed.file.url );
					setTimeout( function() {
						$( 'body' ).trigger( 'closeModal' );
						setTimeout( function() {
							self.$container.removeClass( 'uploading' );
						}, 300 );
					}, 1000 );
				}
			} );

			this.uploader.bind( 'Error', function( up, err ) {
				self.$progressError.html( '<p>' + err.message + '</p>' ).show();
				self.$container.removeClass( 'uploading' );
				up.refresh();
			} );
		}
	};

	DEDO_Existing_Modal = {
		$fileUrl: $( '#dedo-file-url' ),
		$confirm: $( '#dedo-select-done' ),
		$browser: $( '#dedo-file-browser' ),
		$breadcrumbs: $( '<div class="dedo-file-browser-breadcrumbs" />' ),
		options: {},
		currentDir: '',

		init: function( options ) {
			this.options = options;
			this.$browser.before( this.$breadcrumbs );
			this.bindEvents();
			this.focus();
		},

		bindEvents: function() {
			var self = this;

			self.$confirm.on( 'click', function( e ) {
				DEDO_Admin_Download.addFile( self.$fileUrl.val() );
				$( 'body' ).trigger( 'closeModal' );
				e.preventDefault();
			} );

			$( 'body' ).on( 'click', '.dedo-modal-action.select-existing', function() {
				self.$fileUrl.focus();
				self.loadDirectory( '' );
			} );

			self.$browser.on( 'click', '.dedo-file-browser-item.is-directory', function( e ) {
				self.loadDirectory( $( this ).data( 'path' ) || '' );
				e.preventDefault();
			} );

			self.$browser.on( 'click', '.dedo-file-browser-item.is-file', function( e ) {
				self.$browser.find( '.dedo-file-browser-item.is-file' ).removeClass( 'active' );
				$( this ).addClass( 'active' );
				self.$fileUrl.val( $( this ).data( 'url' ) || '' );
				e.preventDefault();
			} );

			self.$breadcrumbs.on( 'click', 'a', function( e ) {
				self.loadDirectory( $( this ).data( 'path' ) || '' );
				e.preventDefault();
			} );
		},

		renderBreadcrumbs: function() {
			var html = '<a href="#" data-path="">' + this.escapeHtml( this.options.rootLabel || 'Uploads' ) + '</a>';
			var current = this.currentDir.replace( /\/+$/, '' );
			var path = '';

			if ( current ) {
				$.each( current.split( '/' ), function( index, segment ) {
					if ( ! segment ) {
						return;
					}
					path += segment + '/';
					html += ' <span class="separator">/</span> <a href="#" data-path="' + path + '">' + DEDO_Existing_Modal.escapeHtml( segment ) + '</a>';
				} );
			}

			this.$breadcrumbs.html( html );
		},

		renderDirectory: function( response ) {
			var self = this;
			var items = '';
			self.currentDir = response.current || '';
			self.renderBreadcrumbs();

			if ( response.parent !== undefined && response.current ) {
				items += '<button type="button" class="dedo-file-browser-item is-directory is-parent button-link" data-path="' + self.escapeAttr( response.parent ) + '">..</button>';
			}

			$.each( response.directories || [], function( index, item ) {
				items += '<button type="button" class="dedo-file-browser-item is-directory button-link" data-path="' + self.escapeAttr( item.path ) + '"><span class="dashicons dashicons-category"></span>' + self.escapeHtml( item.name ) + '</button>';
			} );

			$.each( response.files || [], function( index, item ) {
				items += '<button type="button" class="dedo-file-browser-item is-file button-link" data-path="' + self.escapeAttr( item.path ) + '" data-url="' + self.escapeAttr( item.url ) + '"><span class="dashicons dashicons-media-default"></span>' + self.escapeHtml( item.name ) + '</button>';
			} );

			if ( ! items ) {
				items = '<p class="description">' + self.escapeHtml( 'Keine Dateien oder Ordner gefunden.' ) + '</p>';
			}

			self.$browser.html( items );
		},

		loadDirectory: function( dir ) {
			var self = this;
			self.$browser.html( '<p>Loading...</p>' );
			$.ajax( {
				url: self.options.ajaxURL,
				type: 'POST',
				timeout: 15000,
				dataType: 'json',
				data: {
					action: self.options.action,
					nonce: self.options.nonce,
					dir: dir || ''
				},
				success: function( response ) {
					if ( response.success ) {
						self.renderDirectory( response.data );
					} else {
						self.$browser.html( '<p class="description">' + self.escapeHtml( ( response.data && response.data.message ) || 'Browser konnte nicht geladen werden.' ) + '</p>' );
					}
				},
				error: function() {
					self.$browser.html( '<p class="description">Browser konnte nicht geladen werden.</p>' );
				}
			} );
		},

		focus: function() {
			var self = this;
			$( 'body' ).on( 'click', '.dedo-modal-action.select-existing', function() {
				self.$fileUrl.focus();
			} );
		},

		escapeHtml: function( value ) {
			return String( value || '' ).replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' ).replace( /"/g, '&quot;' ).replace( /'/g, '&#039;' );
		},

		escapeAttr: function( value ) {
			return this.escapeHtml( value );
		}
	};

	DEDO_Admin_Download.init( updateStatusArgs );
	DEDO_Upload_Modal.init( pluploadArgs );
	DEDO_Existing_Modal.init( fileBrowserArgs );
} );
