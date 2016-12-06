'use strict';

var projectName  = 'sixtenpress-isotope',
	version      = '1.1.0',
	destination  = '../../build',
	gulp         = require( 'gulp' ),
	notify       = require( 'gulp-notify' ),
	uglify       = require( 'gulp-uglify' ),
	rename       = require( 'gulp-rename' ),
	zip          = require( 'gulp-zip' ),
	config       = {
		sassPath: 'sass',
		bowerDir: 'bower_components'
	},
	buildInclude = [
		'**',

		// exclude:
		'!node_modules/**/*',
		'!bower_components/**/*',
		'!sass/**/*',
		'!dist/**/*',
		'!node_modules',
		'!bower_components',
		'!sass',
		'!dist',
		'!gulpfile.js',
		'!package.json',
		'!bower.json'
	];

gulp.task( 'bower', function () {
	return bower()
		.pipe( gulp.dest( config.bowerDir ) )
} );

gulp.task( 'assets', function () {
	gulp.src( config.bowerDir + '/sixtenpress/includes/common/**.*' )
		.pipe( gulp.dest( 'includes/common' ) );
} );

gulp.task( 'js', function () {
	gulp.src( 'includes/js/isotope-set.js' )
		.pipe( rename( {
			extname: '.min.js'
		} ) )
		.pipe( uglify( {preserveComments: 'some'} ) )
		.pipe( gulp.dest( 'includes/js' ) )
		.pipe( notify( {message: 'Your js looks so cool.'} ) );
} );

gulp.task( 'watch', function () {
	gulp.watch( 'sass/**/*.scss', ['js'] );
} );

gulp.task( 'zip', function () {
	return gulp.src( buildInclude, { base: '../' } )
		.pipe( zip( projectName + '.' + version + '.zip' ) )
		.pipe( gulp.dest( destination ) );
} );

gulp.task( 'build', [ 'js', 'zip' ] );

gulp.task( 'default', ['js', 'watch'] );
