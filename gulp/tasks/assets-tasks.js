'use strict';

var gulp = require( 'gulp' );

gulp.task( 'assets-tasks', function () {
	var config = require( '../config' ),
		files  = [
			config.paths.bowerDir + '/gulp-tasks/gulp/tasks/*.*',
			config.paths.bowerDir + '/gulp-tasks/gulp/config.js'
		];
	return gulp.src( files )
		.pipe( gulp.dest( config.root + 'gulp' ) );
} );
