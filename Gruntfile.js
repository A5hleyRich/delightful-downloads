module.exports = function( grunt ) {

	// Project configuration.
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		compass: {
			build: {
				options: {
					cssDir: 'assets/css',
					sassDir: 'assets/sass',
					outputStyle: 'compressed'
				}
			}
		},
		watch: {
			sass: {
				files: [ 'assets/sass/*' ],
				tasks: [ 'compass' ]
			}
		}
	} );

	// Load the plugins.
	require( 'load-grunt-tasks' )( grunt );

	// Default task(s).
	grunt.registerTask( 'default', [ 'compass' ] );

};