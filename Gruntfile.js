module.exports = function( grunt ) {

	// Project configuration.
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		compass: {
			dist: {
				options: {
					cssDir: 'assets/css',
					sassDir: 'assets/sass',
					outputStyle: 'compressed'
				}
			}
		},
		uglify: {
			files: {
				expand: true,
				cwd: 'assets/js',
				src: [ '*.js', '!*.min.js' ],
				dest: 'assets/js',
				ext: '.min.js'
			}
		},
		pot: {
			options: {
				text_domain: 'delightful-downloads',
				dest: 'languages/delightful-downloads.pot',
				keywords: [
					'gettext',
					'__',
					'_e',
					'_n:1,2',
					'_x:1,2c',
					'_ex:1,2c',
					'_nx:4c,1,2',
					'esc_attr__',
					'esc_attr_e',
					'esc_attr_x:1,2c',
					'esc_html__',
					'esc_html_e',
					'esc_html_x:1,2c',
					'_n_noop:1,2',
					'_nx_noop:3c,1,2',
					'__ngettext_noop:1,2'
				],
				encoding: 'UTF-8',
				package_name: 'delightful-downloads',
				package_version: '1.5.2',
				msgid_bugs_address: 'hello@ashleyrich.com',
				comment_tag: 'translators:'
			},
			files: [
				{
					expand: true,
					src: [ '**/*.php', '!node_modules/**/*.php' ]
				}
			]
		},
		po2mo: {
			files: {
				src: 'languages/*.po',
				expand: true
			}

		},
		shell: {
			txPull: {
				command: 'tx pull -a --minimum-perc=60'
			},
			txPush: {
				command: 'tx push -s'
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
	grunt.registerTask( 'translate', [ 'pot', 'shell:txPush', 'shell:txPull', 'po2mo' ] );

};