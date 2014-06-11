jQuery( document ).ready( function( $ ) {

	var DEDO_Admin_Global = {

		init: function() {

			this.confirmAction();
		},

		confirmAction: function() {

			var $confirmAction = $( '.dedo_confirm_action' );

			$confirmAction.on( 'click', function( e ) {

				if ( !confirm( $confirmAction.data( 'confirm' ) ) ) {

					e.preventDefault();
				}
			} );
		}
	};

	DEDO_Admin_Global.init();
} );