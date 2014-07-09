jQuery( document ).ready( function( $ ){

	// Main Add/Edit Download Screen
	DEDO_Admin_Download = {

		options: {},

		currentFiles: 0,

		init: function( options ) {
			this.options = options;

			this.updateFileCount();
			this.eventListeners();

			var self = this;

			// Get initial file status
			$( '.dedo-single-file' ).not( '.template' ).each( function() {
				self.updateStatus( this );
			} );
		},

		eventListeners: function() {
			var self = this;

			// URL manualy update
			$( 'body' ).on( 'change', '.dedo-single-file .file-url input[type="text"]', function() {
				self.updateStatus( $( this ).parents( 'tr.dedo-single-file' ) );
			} );

			// Delete
			$( 'body' ).on( 'click', '.dedo-single-file .delete', function() {
				self.deleteFile( this );
			} );
		},

		addFile: function( url ) {
			var newRow = $( '.dedo-single-file.template' ).clone();
			$( newRow ).find( 'input[type="text"]' ).val( url );
			$( newRow ).appendTo( '#dedo-file-container' ).removeClass( 'template' ).show()

			this.updateFileCount();
			this.updateInputNames();
			this.updateStatus( newRow );
			this.toggleViews();
		},

		deleteFile: function( item ) {
			// Get parent row
			var $parent = $( item ).parents( 'tr.dedo-single-file' );

			// Remove table row
			$parent.remove();
			
			this.updateFileCount();
			this.updateInputNames();
			this.toggleViews();
		},

		toggleViews: function() {
			if ( this.currentFiles != 0 ) {
				// Fade add/existing view
				$( '#dedo-new-download' ).hide( 0, function() {
					// Show existing view
					$( '#dedo-existing-download' ).show();
				} );
			}
			else {
				$( '#dedo-existing-download' ).hide( 0, function() {
					// Show existing view
					$( '#dedo-new-download' ).show();
				} );
			}
		},

		updateFileCount: function() {
			this.currentFiles = $( '.dedo-single-file' ).not( '.template' ).length;
		},

		updateInputNames: function() {
			$( '.dedo-single-file' ).not( '.template' ).find( '.file-url input[type="text"]' ).each( function( index ) {
				$( this ).attr( 'name', 'dedo-file-url[' + index + ']' );
			} );
		},

		updateStatus: function( row ) {
			var self = this;
			var url = $( row ).find( '.file-url input[type="text"]' ).val();

			// Set loading spinner
			$( row ).find( '.file-status' ).html( '<span class="spinner"></span>' );

			$.ajax( {
				
				url: self.options.ajaxURL,
				
				data: {
					action: self.options.action,
					nonce: self.options.nonce,
					url: url
				},
				
				dataType: 'json',
				
				success: function( response ) {
					if ( 'success' == response.status ) {
						// Change status icon and file size
						$( row ).find( '.file-status' ).html( '<span class="status ' + response.content.type + '"></span>' );
						$( row ).find( '.file-size' ).html( response.content.size );
					}
					else {
						// Change status icon
						$( row ).find( '.file-status' ).html( '<span class="status warning"></span>' );
						$( row ).find( '.file-size' ).html( '--' );
					}
				}

			} );
		}

	};

	// Existing File Modal
	DEDO_Existing_Modal = {

		init: function() {
			this.fileBrowser();
			this.confirm();
			this.focus();
		},

		fileBrowser: function() {
			// Init file browser
			$( '#dedo-file-browser' ).fileTree( filebrowser_args, function( file ) {
				// User clicked file, update URL field
				var file_path = file.replace( filebrowser_args.root, filebrowser_args.url );
				$( '#dedo-select-url' ).val( file_path );
			} );
		},

		confirm: function() {
			// Done with existing file modal
			$( '#dedo-select-done' ).on( 'click', function( e ) {
				var url = $( '#dedo-select-url' ).val();
				DEDO_Admin_Download.addFile( url );

				$( 'body' ).trigger( 'closeModal' );
			} );
		},

		focus: function() {
			$( '.dedo-modal-action.select-existing' ).on( 'click', function() {
				$( '#dedo-select-url' ).focus();
			} );
		}

	};

	// Upload File Modal
	DEDO_Upload_Modal = {

		uploader: {},

		init: function() {
			// Init pluploader
			this.uploader = new plupload.Uploader( plupload_args );
			this.uploader.init();

			this.uploadListeners();
		},

		uploadListeners: function() {
			// File added to queue
			this.uploader.bind('FilesAdded', function( up, file ) {
				$( '#dedo-progress-error' ).hide();
				$( '#dedo-progress-bar' ).slideDown( 900 );
				
				up.refresh();
				up.start();
			} );
			
			// Progress bar
			this.uploader.bind( 'UploadProgress', function( up, file ) {
				$( '#dedo-progress-bar #dedo-progress-percent' ).css( 'width', file.percent + '%' );
				$( '#dedo-progress-bar #dedo-progress-text' ).html( file.percent + '%' );
			} );	
			
			// File uploaded
			this.uploader.bind( 'FileUploaded', function( up, file, response ) {
				var response = $.parseJSON( response.response );

		 		if( response.error && response.error.code ) {
		 			this.uploader.trigger('Error', {
		            	code : response.error.code,
		            	message : response.error.message,
		            	file : file
		        	});
		 		}
		 		else {
			 		DEDO_Admin_Download.addFile( response.file.url );
			 		
			 		// Close modal and hide progress bar
			 		$( '#dedo-progress-bar' ).slideUp( 900, function() {
			 			$( 'body' ).trigger( 'closeModal' );
			 		} );

		 		}
			} );
			
			// Error
			this.uploader.bind( 'Error', function( up, err ) {
				$( '#dedo-progress-bar' ).hide( 0, function() {
					$( '#dedo-progress-error' ). html( '<p>' + err.message + '</p>' ).show();
				} );
				
				up.refresh();
			} );
		}

	}

	DEDO_Admin_Download.init( updateStatusArgs );
	DEDO_Existing_Modal.init();
	DEDO_Upload_Modal.init();

} );