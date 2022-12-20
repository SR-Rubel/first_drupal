'use strict';

const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const sourcemaps = require('gulp-sourcemaps');
const autoprefixer = require('gulp-autoprefixer');
const babel = require('gulp-babel');

// compiling scss o css
function compileCSS() {
  return gulp.src('./src/scss/app.scss')
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest('./css'));
};
// compiling to js
function compileJS() {
  return gulp.src('./src/js/app.js')
    .pipe(babel({
        presets: ['@babel/env']
    }))
    .pipe(gulp.dest('./js'))
};
// build js and css
function buildStyles() {
  gulp.src('./src/scss/app.scss')
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(sass(({outputStyle: 'compressed'})).on('error', sass.logError))
    .pipe(gulp.dest('./css'));
  gulp.src('./src/js/app.js')
    .pipe(babel({
        presets: ['@babel/env']
    }))
    .pipe(gulp.dest('./js'))
};

exports.compileJS = compileJS;
exports.compileCSS = compileCSS;
exports.buildStyles = buildStyles;

gulp.task('watch', async function(){
  gulp.watch('./src/scss/app.scss', gulp.series('compileCSS'));
  gulp.watch('./src/js/app.js', gulp.series('compileJS'));
});