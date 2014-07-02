jQuery( document ).ready( function( $ ){

	// Init file browser
	$( '#dedo-file-browser' ).fileTree( filebrowser_args, function( file ) {
		// User clicked file
		var file_path = file.replace( filebrowser_args.root, filebrowser_args.url );
		
		// Update url field
		$( '#dedo-select-url' ).val( file_path ).trigger( 'change' );
	} );

	// Done with existing file modal
	$( '#dedo-select-done' ).on( 'click', function( e ) {

		var url = $( '#dedo-select-url' ).val();

		// Close modal and save URL
		if ( url.length > 0 ) {
			
			console.log( url );
		}

		$( 'body' ).trigger( 'closeModal' );
	} );

	// Init pluploader
	var uploader = new plupload.Uploader( plupload_args );
	
	uploader.init();

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
 			
	 		console.log( $.trim( response.file.url ) );
	 		
	 		// Close modal and hide progress bar
	 		$( '#dedo-progress-bar' ).slideUp( 900, function() {

	 			$( 'body' ).trigger( 'closeModal' );
	 		} );

 		}
	} );
	
	// Error
	uploader.bind( 'Error', function( up, err ) {
		
		console.log( err.message );

		$( '#dedo-progress-bar' ).hide( 0, function() {

			$( '#dedo-progress-error' ). html( '<p>' + err.message + '</p>' ).show();
		} );
		
		up.refresh();
	} );

} );