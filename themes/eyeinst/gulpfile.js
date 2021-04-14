'use strict';

/** Uses the following libraries
    autoprefixer      : "6.7.7"
    browser-sync      : "2.18.8"
    gulp              : "4.0.2"
    gulp-sass         : "3.1.0"
    gulp-util         : "3.0.8"
    gulp-postcss      : "6.4.0"
    gulp-jshint       : "2.0.4"
    jshint-stylish    : "2.2.1"
    gulp-newer        : "1.3.0"
*/

/**
 * Gulp and plugins
 */


const gulp          = require('gulp');// still have to run npm install for gulp
const gutil         = require('gulp-util');
const sass 					= require('gulp-dart-sass');
const postcss       = require('gulp-postcss');
const autoprefixer  = require('autoprefixer');
const browserSync   = require('browser-sync').create();
const jshint        = require('gulp-jshint');
const newer         = require('gulp-newer');


/**
 * src paths
 */

const _SRC = {
    js : 'js/scripts.js',
    sass : 'sass/**/*.scss'
};

/** 
 * BrowserSync
*/

function _brosersync(done){
    const myPort  = 8080; //update this to your port
    const myHost  = '127.0.0.1';//update this to your local ip
    browserSync.init({
        ui          : false,
        port        : myPort,
        files       : ['**/*.php'],
        host        : myHost,
        open        : false,
        notify      : false,
        ghost       : false,
        socket: { domain: myHost +":"+ myPort }
    });   
    done();
}

/**
 * Javascript
 */

function _JS(){
    return gulp.src(_SRC.js)
    .pipe(jshint())
    //.pipe(jshint.reporter('jshint-stylish'))
    .pipe(browserSync.reload({stream: true}));
}

/** 
 * Styling 
*/

function _CSS(done){
    return gulp.src(_SRC.sass)
    .pipe(sass({outputStyle: 'expanded'}).on('error', sass.logError))
    .pipe(postcss([autoprefixer('last 2 version', 'safari 5', 'ie 8', 'ie 9', 'opera 12.1')]))
    .pipe(gulp.dest("./"))
    .pipe(browserSync.reload({stream: true}));
};

/**
 * WATCH
 */ 

function watchFiles(){
	gulp.watch(_SRC.sass , _CSS);
	gulp.watch(_SRC.js , _JS);
}

/** 
 * Run Task: 
 * + Watch CSS / JS
 * + Run LiveReload
*/
	
gulp.task("watch",gulp.series( _brosersync , watchFiles));

