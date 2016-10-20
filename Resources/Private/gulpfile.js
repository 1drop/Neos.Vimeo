'use strict';

/* Needed gulp config */
var gulp = require('gulp');
var sass = require('gulp-sass');
var cssnano = require('cssnano');
var autoprefixer = require('autoprefixer');
var mainBowerFiles = require('gulp-main-bower-files');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var concat = require('gulp-concat');
var sourcemaps = require('gulp-sourcemaps');
var plumber = require('gulp-plumber');
var postcss = require('gulp-postcss');
var gulpFilter = require('gulp-filter');

var basePath = '.';

var paths = {
    watch_dir:   basePath,
    bower_dir:   basePath + '/Bower/',
    sass_dir:    basePath + '/Scss/',
    js_dir:      basePath + '/JavaScript/',
    dist: {
        scripts: basePath + '/../Public/JavaScript/',
        styles:  basePath + '/../Public/Styles/'
    }
};

gulp.task('bower', function() {
    var filterJS = gulpFilter('**/*.js', { restore: true });
   return gulp.src(paths.bower_dir + 'bower.json')
       .pipe(mainBowerFiles())
       .pipe(filterJS)
       .pipe(sourcemaps.init())
       .pipe(concat('vendor.js'))
       .pipe(uglify())
       .pipe(sourcemaps.write('.'))
       .pipe(gulp.dest(paths.dist.scripts));
});

/* Scripts task */
gulp.task('scripts', ['bower'], function() {
    return gulp.src(paths.js_dir + '*.js')
        .pipe(sourcemaps.init())
        .pipe(concat('main.js'))
        .pipe(uglify())
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(paths.dist.scripts));
});

/* Sass task */
gulp.task('sass', function () {
    var processors = [
        autoprefixer({browsers: ['last 2 version']}),
        cssnano()
    ];
    gulp.src(paths.sass_dir + '*.scss')
        .pipe(sourcemaps.init())
        .pipe(plumber())
        .pipe(sass())
        .pipe(postcss(processors))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(paths.dist.styles))
});

gulp.task('build', ['sass', 'scripts']);

/* Watch scss, js and html files, doing different things with each. */
gulp.task('default', ['sass'], function () {
    /* Watch scss, run the sass task on change. */
    gulp.watch([paths.sass_dir + '**/*'], ['sass']);
    /* Watch app.js file, run the scripts task on change. */
    gulp.watch([paths.js_dir + '**/*.js'], ['scripts']);
    /* Watch app.js file, run the scripts task on change. */
    gulp.watch([paths.bower_dir + 'bower.json'], ['build']);
});
