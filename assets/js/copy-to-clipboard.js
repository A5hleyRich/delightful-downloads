/**
 * Copy to clipboard
 */
jQuery( document ).ready( function( $ ) {
	$inputs = $( '.copy-to-clipboard' );

	$inputs.on( 'click', function( e ) {
		var $input  = $( this );
		var $notice = $input.siblings( 'p' );

		try {
			$input.select();
			document.execCommand('copy');

			$notice.fadeIn();

			setTimeout(function() {
				$notice.fadeOut();
			}, 3000);
		} catch ( error ) {
			console.log( 'Unable to copy' );
		}

	} );
} );