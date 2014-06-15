jQuery( document ).ready( function( $ ) {

	/**
	 * Migrate Legacy Logs
	 *
	 * Handles the migration of legacy logs on the 
	 * statistics screen.
	 */
	var DEDO_Legacy_Logs = {

		// Store options from WP serialized array
		options: {},

		migrateContainer : $( '#dedo_migrate_message' ),
		migrateButton: $( '#dedo_migrate_button' ),
		migrateSpinner: $('#dedo_migrate_message .spinner'),
		migrateCount: $('#dedo_migrate_count'),
		
		// Setup our object
		init: function( options ) {
			this.options = options;
			this.toggle();
			this.start();
			this.stop();
			this.success();
			this.error();
		},
		
		// Toggle between start/stop
		toggle: function() {
			var self = this;

			this.migrateButton.on( 'click', function() {
	
				if ( false === self.migrating || 'undefined' === typeof self.migrating ) {
					$( document ).trigger( 'dedo_migrate_start' );
				}
				else {
					$( document ).trigger( 'dedo_migrate_stop' );
				}

			} );
		},

		// Add event listener to start migration
		start: function() {
			var self = this;

			$( document ).on( 'dedo_migrate_start', function() {
			
				// Change button and set migrating flag
				self.migrateButton.attr( 'value', self.options.stop_text );
				self.migrating = true;

				// Show spinner
				self.migrateSpinner.css( 'display', 'inline-block' );

				// Send ajax request, delete 100ish logs
				$.ajax( {
					url: self.options.ajaxurl,
					data: {
						action: self.options.action,
						nonce: self.options.nonce
					},
					dataType: 'json',
					success: function( response ) {
						// All good, trigger success and pass current count
						if ( 'success' === response.status ) {
							$( document ).trigger( 'dedo_migrate_success', response.content );
						}
						// Error, trigger error
						else {
							$( document ).trigger( 'dedo_migrate_error' );
						}
					}
				} );

			} );
		},

		// Add event listener to stop migration
		stop: function() {
			var self = this;

			$( document ).on( 'dedo_migrate_stop', function() {
				// Change button and set migrating flag
				self.migrateButton.attr( 'value', self.options.migrate_text );
				self.migrating = false;

				// Hide spinner
				self.migrateSpinner.css( 'display', 'none' );
			} );
		},

		// Add event listener for successful migration
		success: function() {
			var self = this;

			$( document ).on( 'dedo_migrate_success', function( e, current_count ) {
				self.migrateCount.text( current_count );

				// Run again if more logs exist and stopped not clicked
				if ( current_count > 0 && true === self.migrating ) {
					$( document ).trigger( 'dedo_migrate_start' );
				}

				// Show success
				if ( 0 === current_count ) {
					self.migrateContainer.removeClass( 'error' ).addClass( 'updated' );
					self.migrateButton.parent().slideUp( 'fast' );
				}
			} );

		},

		// Add event listener for migration error
		error: function() {
			var self = this;

			$( document ).on( 'dedo_migrate_error', function() {
				$( document ).trigger( 'dedo_migrate_stop' );
				alert( self.options.error_text );
			} );
		}

	};
	
	// Init and pass options from migrate.php wp_localize_script()
	if ( 'undefined' !== typeof dedo_admin_logs_migrate ) {
		DEDO_Legacy_Logs.init( dedo_admin_logs_migrate );
	}

} );