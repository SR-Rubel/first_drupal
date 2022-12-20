'use strict';

const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const sourcemaps = require('gulp-sourcemaps');
const autoprefixer = require('gulp-autoprefixer');
const concat = require('gulp-concat');

function compile() {
  return gulp.src('./src/scss/app.scss')
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest('./css'));
};
function buildStyles() {
  return gulp.src('./src/scss/app.scss')
    .pipe(autoprefixer({
      cascade: false
    }))
    .pipe(sass(({outputStyle: 'compressed'})).on('error', sass.logError))
    .pipe(gulp.dest('./css'));
};

exports.compile = compile;
exports.buildStyles = buildStyles;

gulp.task('watch', function(){
  gulp.watch('./src/scss/app.scss', gulp.series('compile'));
});