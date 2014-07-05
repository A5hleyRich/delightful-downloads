jQuery( document ).ready( function( $ ){

	// Main Add/Edit Download Screen
	DEDO_Admin_Download = {

		init: function() {
			this.clickListeners();
		},

		clickListeners: function() {
			var self = this;

			// Delete
			$( '#dedo-remove-button' ).on( 'click', function() {
				self.deleteFile();
			} );
		},

		addFile: function( url ) {
			// Update text field
			$( '#dedo-file-url' ).val( url );
			this.toggleViews();
		},

		deleteFile: function() {
			// Update text field
			$( '#dedo-file-url' ).val( '' );
			this.toggleViews();
		},

		toggleViews: function() {
			// Fade add/existing view
			$( '#dedo-new-download' ).toggle( 0, function() {
				// Show existing view
				$( '#dedo-existing-download' ).toggle();
			} );
		}

	};

	// Existing File Modal
	DEDO_Existing_Modal = {

		init: function() {
			this.fileBrowser();
			this.confirm();	
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

				// Close modal and save URL
				if ( url.length > 0 ) {
					DEDO_Admin_Download.addFile( url );
				}

				$( 'body' ).trigger( 'closeModal' );
			} );
		}

	};

	// Upload File Modal
	DEDO_Upload_Modal = {

		init: function() {
			// Init pluploader
			var uploader = new plupload.Uploader( plupload_args );
			uploader.init();

			this.uploadListeners();
		},

		uploadListeners: function() {
			// File added to queue
			uploader.bind('FilesAdded', function( up, file ) {
				$( '#dedo-progress-error' ).hide();
				$( '#dedo-progress-bar' ).slideDown( 900 );
				
				up.refresh();
				up.start();
			} );
			
			// Progress bar
			uploader.bind( 'UploadProgress', function( up, file ) {
				$( '#dedo-progress-bar #dedo-progress-percent' ).css( 'width', file.percent + '%' );
				$( '#dedo-progress-bar #dedo-progress-text' ).html( file.percent + '%' );
			} );	
			
			// File uploaded
			uploader.bind( 'FileUploaded', function( up, file, response ) {
				var response = $.parseJSON( response.response );

		 		if( response.error && response.error.code ) {
		 			uploader.trigger('Error', {
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
			uploader.bind( 'Error', function( up, err ) {
				$( '#dedo-progress-bar' ).hide( 0, function() {
					$( '#dedo-progress-error' ). html( '<p>' + err.message + '</p>' ).show();
				} );
				
				up.refresh();
			} );
		}

	}

	DEDO_Admin_Download.init();
	DEDO_Existing_Modal.init();
	DEDO_Upload_Modal.init();

} );