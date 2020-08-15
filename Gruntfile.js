module.exports = function( grunt ) {

	// Project configuration.
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),
		compass: {
			dist: {
				options: {
					cssDir: 'src/delightful-downloads/assets/css',
					sassDir: 'src/delightful-downloads/assets/sass',
					environment: 'development'
				}
			}
		},
		cssmin: {
			target: {
				files: [{
					expand: true,
					cwd: 'src/delightful-downloads/assets/css',
					src: [ '*.css', '!*.min.css' ],
					dest: 'src/delightful-downloads/assets/css',
					ext: '.min.css'
				}]
			}
		},
		uglify: {
			files: {
				expand: true,
				cwd: 'src/delightful-downloads/assets/js',
				src: [ '*.js', '!*.min.js' ],
				dest: 'src/delightful-downloads/assets/js',
				ext: '.min.js'
			}
		},
		pot: {
			options: {
				text_domain: 'delightful-downloads',
				dest: 'src/delightful-downloads/languages/delightful-downloads.pot',
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
				package_version: '1.6.1',
				msgid_bugs_address: 'hello@ashleyrich.com',
				comment_tag: 'translators:'
			},
			files: {
				expand: true,
				src: [ 'src/delightful-downloads/**/*.php' ]
			}
		},
		po2mo: {
			files: {
				src: 'src/delightful-downloads/languages/*.po',
				expand: true
			}

		},
		shell: {
			txPull: {
				command: 'tx pull -a --minimum-perc=90'
			},
			txPush: {
				command: 'tx push -s'
			}
		},
		clean: ['src/delightful-downloads/languages/*.po'],
		watch: {
			sass: {
				files: [ 'src/delightful-downloads/assets/sass/*' ],
				tasks: [ 'compass' ]
			},
			css: {
				files: [ 'src/delightful-downloads/assets/css/*', '!src/delightful-downloads/assets/css/*.min.css' ],
				tasks: [ 'cssmin' ]
			},
			js: {
				files: [ 'src/delightful-downloads/assets/js/*.js', '!src/delightful-downloads/assets/js/*.min.js' ],
				tasks: [ 'uglify' ]
			}
		}
	} );

	// Load the plugins.
	require( 'load-grunt-tasks' )( grunt );

	// Default task(s).
	grunt.registerTask( 'default', [ 'compass', 'cssmin', 'uglify' ] );
	grunt.registerTask( 'translate', [ 'pot', 'shell:txPush', 'shell:txPull', 'po2mo', 'clean' ] );

};