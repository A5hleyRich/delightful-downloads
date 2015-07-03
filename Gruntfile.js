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
		uglify: {
			build: {
				files: [
					{
						expand: true,
						cwd: 'assets/js',
						src: [ '*.js', '!*.min.js' ],
						dest: 'assets/js',
						ext: '.min.js'
					}
				]
			}
		},
		watch: {
			sass: {
				files: [ 'assets/sass/*' ],
				tasks: [ 'compass' ]
			},
			js: {
				files: [ 'assets/js/*.js', '!assets/js/*.min.js' ],
				tasks: [ 'uglify' ]
			}
		}
	} );

	// Load the plugins.
	require( 'load-grunt-tasks' )( grunt );

	// Default task(s).
	grunt.registerTask( 'default', [ 'compass', 'uglify' ] );

};