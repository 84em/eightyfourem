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
    theme: [
      './assets/css/customizer.css',
      './assets/css/sticky-header.css',
      './assets/css/case-study-filter.css'
    ],
    googleReviews: [
      './assets/google-reviews-block/style.css',
      './assets/google-reviews-block/editor.css'
    ],
    breadcrumbs: './assets/css/breadcrumbs.css',
    highlight: './assets/css/highlight.css',
    dest: './assets/css/'
  },
  scripts: {
    theme: [
      './assets/js/sticky-header.js',
      './assets/js/case-study-filter.js'
    ],
    googleReviews: './assets/google-reviews-block/block.js',
    highlight: './assets/js/highlight.js',
    dest: './assets/js/'
  }
};

// Clean task - removes previously generated .min files
function clean() {
  return deleteAsync([
    './assets/css/*.min.css',
    './assets/css/*.min.css.map',
    './assets/js/*.min.js',
    './assets/js/*.min.js.map',
    './assets/google-reviews-block/*.min.css',
    './assets/google-reviews-block/*.min.css.map',
    './assets/google-reviews-block/*.min.js',
    './assets/google-reviews-block/*.min.js.map'
  ]);
}

// CSS optimization task - Theme files
function stylesTheme() {
  return gulp.src(paths.styles.theme)
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

// CSS optimization task - Google Reviews
function stylesGoogleReviews() {
  return gulp.src(paths.styles.googleReviews)
    .pipe(sourcemaps.init())
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
    .pipe(gulp.dest('./assets/google-reviews-block/'));
}

// CSS optimization task - Breadcrumbs
function stylesBreadcrumbs() {
  return gulp.src(paths.styles.breadcrumbs)
    .pipe(sourcemaps.init())
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

// CSS optimization task - Highlight
function stylesHighlight() {
  return gulp.src(paths.styles.highlight)
    .pipe(sourcemaps.init())
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

// Combined styles task
const styles = parallel(stylesTheme, stylesGoogleReviews, stylesBreadcrumbs, stylesHighlight);

// JavaScript optimization task - Theme files
function scriptsTheme() {
  return gulp.src(paths.scripts.theme)
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

// JavaScript optimization task - Google Reviews
function scriptsGoogleReviews() {
  return gulp.src(paths.scripts.googleReviews)
    .pipe(sourcemaps.init())
    .pipe(terser({
      compress: {
        drop_console: true
      }
    }))
    .pipe(rename({ suffix: '.min' }))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest('./assets/google-reviews-block/'));
}

// JavaScript optimization task - Highlight
function scriptsHighlight() {
  return gulp.src(paths.scripts.highlight)
    .pipe(sourcemaps.init())
    .pipe(terser({
      compress: {
        drop_console: true
      }
    }))
    .pipe(rename({ suffix: '.min' }))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(paths.scripts.dest));
}

// Combined scripts task
const scripts = parallel(scriptsTheme, scriptsGoogleReviews, scriptsHighlight);

// Watch task for development
function watchFiles() {
  watch(paths.styles.theme, stylesTheme);
  watch(paths.styles.googleReviews, stylesGoogleReviews);
  watch(paths.styles.breadcrumbs, stylesBreadcrumbs);
  watch(paths.styles.highlight, stylesHighlight);
  watch(paths.scripts.theme, scriptsTheme);
  watch(paths.scripts.googleReviews, scriptsGoogleReviews);
  watch(paths.scripts.highlight, scriptsHighlight);
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
