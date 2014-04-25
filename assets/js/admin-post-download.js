jQuery( document ).ready( function( $ ){
	
	// Init file browser
	$( '#dedo-file-browser' ).fileTree( filebrowser_args, function( file ) {
		var file_path = file.replace( filebrowser_args.root, filebrowser_args.url );
		$( '#dedo-file-url' ).val( file_path );
	} );
	
	// Toggle file browser
	$( '#dedo-select-button' ).click( function() {
		$( '#dedo-file-browser' ).slideToggle();
	} );
	
	// Init pluploader
	var uploader = new plupload.Uploader( plupload_args );
    uploader.init();
	
	 // File added to queue
	uploader.bind('FilesAdded', function( up, file ) {
		$( '#plupload-error' ).hide();
		$( '#plupload-progress' ).slideDown();
		
		up.refresh();
		up.start();
	} );
	
	// Progress bar
	uploader.bind( 'UploadProgress', function( up, file ) {
		$( '#plupload-progress .bar' ).css( 'width', file.percent + '%' );
		$( '#plupload-progress .percent' ).html( '<p>' + file.percent + '%</p>' );
	} );	
	
	// File uploaded
	uploader.bind( 'FileUploaded', function( up, file, response ) {
		response = $.parseJSON( response.response );

 		if( response.error && response.error.code ) {
 			uploader.trigger('Error', {
            	code : response.error.code,
            	message : response.error.message,
            	details : response.details,
            	file : file
        	});
 		}
 		else {
 			$( '#dedo-file-url' ).val( $.trim( response.file.url ) );
			$( '#plupload-file-size' ).html( plupload.formatSize( file['size'] ) );
			$( '#plupload-progress' ).slideUp();
 		}
	} );
	
	// Error
	uploader.bind( 'Error', function( up, err ) {
		$( '#plupload-error' ).show().html( err.message );
		$( '#plupload-progress' ).slideUp();
		
		up.refresh();
	} );

} );