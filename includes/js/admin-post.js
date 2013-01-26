jQuery( document ).ready( function( $ ){
	
	var download_id;
	
	// Display download modal
	$( '#dedo-media-button' ).click( function( e ) {
		$( '#dedo-download-modal' ).show();
		
		e.preventDefault();
	} );
	
	// Close download modal
	$( '#dedo-download-modal-close' ).click( function( e ) {
		$( '#dedo-download-modal' ).hide();
		
		e.preventDefault();
	} );
	
	$( '.media-modal-backdrop' ).click( function() {
		$( '#dedo-download-modal' ).hide();
	} );
	
	// Search list
	$( '#dedo-download-search' ).keyup( function() {
		var searchField = $( this ).val();
		
		// Loop through download items
		$( '#selectable_list li' ).each( function() {
			if( $( this ).children( 'strong' ).text().search( new RegExp( searchField, 'i' ) ) < 0 ) {
				$( this ).fadeOut( 'fast' );
			}
			else {
				$( this ).fadeIn( 'fast' );
			}
		} );
	} );
	
	// Clear search
	var search = document.getElementById( 'dedo-download-search' );
	
	search.addEventListener( 'search', function( e ) {
		if( $( '#dedo-download-search' ).val().length == 0 ) {
			$( '#selectable_list' ).children().fadeIn( 'fast' );
		}
	}, false );
	
	// Total download count button
	$( '#dedo-total-count-button' ).click( function( e ) {
		// Add to editor
		window.send_to_editor( '[ddownload_total_count]' );
		e.preventDefault();
		
		// Hide modal
		$( '#dedo-download-modal' ).hide();
	} ); 
	
	// Selectable list items
	var selectableOpts = {
		selected: function( e, ui ) {
			download_id = $( ui.selected ).attr( 'data-ID' );
			download_title = $( ui.selected ).children( 'strong' ).text();
			download_size = $( ui.selected ).attr( 'data-size' );
			$( '.download-details .meta' ).html( '<strong>' + download_title + '</strong><div>' + download_size + '</div>' );
			$( '.download-details' ).show();
		}
	};
	
	// Set selectable item
	$( '#selectable_list' ).selectable( selectableOpts );
	
	// Download insert button
	$( '#dedo-download-button' ).click( function( e ) {
		var download_text = $( '#dedo-download-text' ).val();
		var download_style = $( '#dedo-download-style' ).val();
		var download_color = $( '#dedo-download-color' ).val();
		
		// Check if button and add color
		if( download_style == 'button' ) {
			color = ' color="' + download_color + '"'
		}
		else {
			color = ''
		}
		
		// Add to editor
		window.send_to_editor( '[ddownload id="' + download_id + '" text="' + download_text + '" style="' + download_style + '"' + color + ']' );
		e.preventDefault();
		
		// Hide modal
		$( '#dedo-download-modal' ).hide();
	} );
	
	// Download filesize button
	$( '#dedo-filesize-button' ).click( function( e ) {
		// Add to editor
		window.send_to_editor( '[ddownload_size id="' + download_id + '"]' );
		e.preventDefault();
		
		// Hide modal
		$( '#dedo-download-modal' ).hide();
	} );
	
	// Download count button
	$( '#dedo-count-button' ).click( function( e ) {
		// Add to editor
		window.send_to_editor( '[ddownload_count id="' + download_id + '"]' );
		e.preventDefault();
		
		// Hide modal
		$( '#dedo-download-modal' ).hide();
	} ); 
	
	// Hide/show color select
	if( $( '#dedo-download-style' ).val() == 'button' ) {
		$( '.dedo-download-color-container' ).show();
	}
	else {
		$( '.dedo-download-color-container' ).hide();	
	}
	
	// Hide/show color select on change
	$( '#dedo-download-style' ).change( function() {
		if( $( '#dedo-download-style' ).val() == 'button' ) {
			$( '.dedo-download-color-container' ).slideDown();
		}
		else {
			$( '.dedo-download-color-container' ).slideUp();	
		}
	} );     

} );