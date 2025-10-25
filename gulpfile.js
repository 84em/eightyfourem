/**
 * Gulp configuration file for eightyfourem theme
 *
 * This file defines tasks for optimizing CSS and JS files in the theme.
 */

const gulp = require('gulp');
const cleanCSS = require('gulp-clean-css');
const terser = require('gulp-terser');
const rename = require('gulp-rename');
const sourcemaps = require('gulp-sourcemaps');
const autoprefixer = require('gulp-autoprefixer').default;
const { series, parallel, watch } = require('gulp');
const { deleteAsync } = require('del');

// File paths
const paths = {
  styles: {
    src: [
      './assets/css/customizer.css',
      './assets/css/sticky-header.css'
    ],
    dest: './assets/css/'
  },
  scripts: {
    src: './assets/js/sticky-header.js',
    dest: './assets/js/'
  }
};

// Clean task - removes previously generated .min files
function clean() {
  return deleteAsync([
    './assets/css/*.min.css',
    './assets/css/*.min.css.map',
    './assets/js/*.min.js',
    './assets/js/*.min.js.map'
  ]);
}

// CSS optimization task
function styles() {
  return gulp.src(paths.styles.src)
    .pipe(sourcemaps.init())
    .pipe(autoprefixer({
      overrideBrowserslist: ['last 2 versions'],
      cascade: true
    }))
    .pipe(cleanCSS({
      compatibility: 'ie8',
      level: {
        1: {
          specialComments: 0
        }
      }
    }))
    .pipe(rename({ suffix: '.min' }))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(paths.styles.dest));
}

// JavaScript optimization task
function scripts() {
  return gulp.src(paths.scripts.src)
    .pipe(sourcemaps.init())
    .pipe(terser({
      compress: {
        drop_console: false
      }
    }))
    .pipe(rename({ suffix: '.min' }))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(paths.scripts.dest));
}

// Watch task for development
function watchFiles() {
  watch(paths.styles.src, styles);
  watch(paths.scripts.src, scripts);
}

// Define complex tasks
const build = series(clean, parallel(styles, scripts));
const dev = series(build, watchFiles);

// Export tasks
exports.clean = clean;
exports.styles = styles;
exports.scripts = scripts;
exports.watch = watchFiles;
exports.build = build;
exports.default = dev;
