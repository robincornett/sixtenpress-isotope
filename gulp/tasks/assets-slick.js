'use strict';

var gulp = require( 'gulp' );

gulp.task( 'assets-slick', function () {
	var config = require( '../config' );
	return gulp.src( config.paths.bowerDir + '/slick-carousel/slick/**.js' )
		.pipe( gulp.dest( config.output.scriptDestination ) );
} );
