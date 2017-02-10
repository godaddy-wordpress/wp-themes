/* global module, require */

module.exports = function( grunt ) {

	'use strict';

	grunt.initConfig( {

		devUpdate: {
			packages: {
				options: {
					packageJson: null,
					packages: {
						devDependencies: true,
						dependencies: false
					},
					reportOnlyPkgs: [],
					reportUpdated: false,
					semver: true,
					updateType: 'force'
				}
			}
		},

		jshint: {
			gruntfile: [ 'Gruntfile.js']
		},

		jsonlint: {
			options: {
				format: false,
				indent: 0
			},
			manifest: {
				src: [ 'manifest.json' ]
			},
			min: {
				src: [ 'manifest.min.json' ]
			}
		},

		json_minification: {
			manifest: {
				files: {
					'manifest.min.json': 'manifest.json'
				}
			}
		}

	} );

	require( 'matchdep' ).filterDev( 'grunt-*' ).forEach( grunt.loadNpmTasks );

	grunt.registerTask( 'default', [ 'jshint', 'jsonlint:manifest', 'json_minification', 'jsonlint:min' ] );
	grunt.registerTask( 'check',   [ 'devUpdate' ] );

};
