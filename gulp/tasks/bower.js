'use strict';

var gulp = require( 'gulp' );

gulp.task( 'bower', function () {
	return bower()
		.pipe( gulp.dest( config.paths.bowerDir ) )
} );
