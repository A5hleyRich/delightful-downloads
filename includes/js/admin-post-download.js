jQuery( document ).ready( function( $ ){
	
	var uploader = new plupload.Uploader( plupload_args );
	
    // User clicks upload
    $( '#plupload-upload-button' ).click( function( e ) {
        $( '#plupload-upload-button' ).fadeOut( 'fast' );
		$( '#plupload-cancel-button' ).fadeOut( 'fast' );
        $( '#plupload-progress' ).slideDown( 'fast', function() {
	        uploader.start();
        } );
        
        e.preventDefault();
    } );
    
    // User clicks cancel
    $( '#plupload-cancel-button' ).click( function( e ) {
        // Empty queue
        uploader.splice();
        e.preventDefault();
        
        $( '#plupload-file' ).html( '----' );
		$( '#plupload-file-size' ).html( '----' );
		$( '#plupload-browse-button' ).removeAttr( 'disabled' );
		$( '#plupload-upload-button' ).fadeOut( 'fast' );
		$( '#plupload-cancel-button' ).fadeOut( 'fast' );
        
    } );
    
    // Init
    uploader.init();
    
    // File added to queue
	uploader.bind('FilesAdded', function( up, files ) {
		plupload.each( files, function( file ) {
			$( '#plupload-file' ).html( file.name );
			$( '#plupload-file-size' ).html( plupload.formatSize( file.size ) );
		} );
		
		$( '#plupload-browse-button' ).attr( 'disabled', 'disabled' );
		$( '#plupload-upload-button' ).fadeIn( 'fast' );
		$( '#plupload-cancel-button' ).fadeIn( 'fast' );
		
		up.refresh();
	} );
	
	// Error
	uploader.bind( 'Error', function( up, err ) {
		$( '#plupload-file' ).html( '<span class="error">' + err.message + '</span>' );
		$( '#plupload-file-size' ).html( '----' );
		
		up.refresh();
	} );
	
	// Progress bar
	uploader.bind( 'UploadProgress', function( up, file ) {
		$( '#plupload-progress .bar' ).css( 'width', file.percent + '%' );
		$( '#plupload-progress .percent' ).html( '<p>' + file.percent + '%</p>' );
	} );
	
	// File uploaded
	uploader.bind( 'FileUploaded', function( up, file, response ) {
		$( '#plupload-browse-button' ).removeAttr( 'disabled' );
		$( '#plupload-upload-button' ).fadeOut( 'fast' );
		$( '#plupload-cancel-button' ).fadeOut( 'fast' );
		$( '#plupload-progress' ).slideUp( 'fast' );
		$( '#plupload-file' ).html( '<span class="success">' + response.response + '</span>' );

	} );

} );