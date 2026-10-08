/**
 * Copy to clipboard.
 */
jQuery( function( $ ) {
	function fallbackCopy( element ) {
		element.select();
		return document.execCommand( 'copy' );
	}

	function copyInputValue( element ) {
		var text = element.value || '';

		if ( ! text ) {
			return Promise.reject( new Error( 'Nothing to copy' ) );
		}

		if ( navigator.clipboard && window.isSecureContext ) {
			return navigator.clipboard.writeText( text );
		}

		return new Promise( function( resolve, reject ) {
			try {
				fallbackCopy( element );
				resolve();
			} catch ( error ) {
				reject( error );
			}
		} );
	}

	var $inputs = $( '.copy-to-clipboard' );
	$inputs.on( 'click', function() {
		var $input  = $( this );
		var $notice = $input.siblings( 'p' );

		copyInputValue( this )
			.then( function() {
				$notice.stop( true, true ).fadeIn();
				setTimeout( function() {
					$notice.fadeOut();
				}, 3000 );
			} )
			.catch( function( error ) {
				console.error( 'Unable to copy to clipboard', error );
			} );
	} );
} );
