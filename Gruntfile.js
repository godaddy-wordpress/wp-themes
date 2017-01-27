/* global module, require */

module.exports = function( grunt ) {

	'use strict';

	grunt.initConfig( {

		devUpdate: {
			options: {
				updateType: 'force',
				reportUpdated: false,
				semver: true,
				packages: {
					devDependencies: true,
					dependencies: false
				},
				packageJson: null,
				reportOnlyPkgs: []
			}
		},

		jshint: {
			all: [ 'Gruntfile.js', 'manifest.json' ]
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

	grunt.registerTask( 'default', [ 'jshint', 'json_minification' ] );

};
