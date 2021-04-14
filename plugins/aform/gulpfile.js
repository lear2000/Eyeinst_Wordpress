'use strict';

const gulp = require( 'gulp' );
const watch = require( 'gulp-watch' );
const sass = require( 'gulp-sass' );
const autoprefixer = require( 'gulp-autoprefixer' );
const jshint = require( 'gulp-jshint' );
const webpack = require('webpack');
const uglify = require( 'gulp-uglify' );
const webpackConfig = require('./webpack.config.js');
const webpackRun = webpack(webpackConfig);	
	
// var onError = function( err ) {
// 	console.log( 'Aww shit, man, somethin\'s fucked: ', err.message );
// 	this.emit( 'end' );
// }

// gulp.task( 'sass', function() {
// 	gulp.src('./assets/_build/sass/**/*.scss')
// 		.pipe( sass({outputStyle: 'compressed'}).on( 'error', sass.logError))
// 		.pipe( autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1') )
// 		.pipe(gulp.dest('./assets/css' ));
// });

// gulp.task( 'jshint', function() {
// 	return gulp.src('./assets/_build/js/**/*.js')
// 		.pipe( jshint() )
// 		.pipe( uglify() )
// 		.pipe( gulp.dest('./assets/js') );
// });

// gulp.task('webpack', function(done) {
//   webpackRun.run(function(err, stats) {
//     if(err) {
//       console.log('Error', err);
//     }
//     else {
//       console.log(stats.toString());
//     }
//     done();
//   });
// });

var _webpack = function(done){
	webpackRun.run(function(err, stats) {
		if(err) {
		  console.log('Error', err);
		}
		else {
		  console.log(stats.toString());
		}
		// done();
	});
	done();
};

// gulp.task( 'jshint', function() {
// 	return gulp.src('./assets/_build/js/**/*.js')
// 		.pipe( jshint() )
// 		.pipe( uglify() )
// 		.pipe( gulp.dest('./assets/js') );
// });

// /* JS Hint */

var jsFiles = {
	src : './assets/_build/js/**/*.js',
	dest : './assets/js'
}

function _scripts(){
    return gulp.src( jsFiles.src )
    .pipe( jshint() )
		.pipe( uglify() )
		.pipe( gulp.dest( jsFiles.dest ));
		//
}

function _watcher(  ){
	gulp.watch( jsFiles.src , _scripts );
	//gulp.watch( jsFiles.src , _webpack );

};


gulp.task("watch",gulp.series( _watcher ));

// gulp.task( 'watch', function() {
// 	gulp.watch( './assets/_build/sass/**/*.scss', ['sass'] );
// 	gulp.watch( './assets/_build/js/**/*.js', ['jshint'] );
// 	gulp.watch( './assets/_build/vue/**/*.js', ['webpack'] );
// });

// gulp.task( 'default', ['watch'] );