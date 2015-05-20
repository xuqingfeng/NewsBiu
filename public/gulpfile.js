var gulp = require('gulp'),
    minifyCss = require('gulp-minify-css'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename');

gulp.task('default', ['minify-css', 'minify-js', 'copy-img']);


//gulp.task('default', ['watch']);
// bug: no change, so do nothing
//gulp.task('watch', function () {
//    gulp.watch([
//        './dev/**'
//    ], [
//        'minify-css',
//        'minify-js',
//        'copy-img'
//    ]);
//});

gulp.task('minify-css', function () {
    gulp.src('./dev/css/*.css')
        .pipe(minifyCss())
        .pipe(rename({extname: '.min.css'}))
        .pipe(gulp.dest('./prd/css'));
});

gulp.task('minify-js', function () {
    gulp.src('./dev/js/*.js')
        .pipe(uglify())
        .pipe(rename({extname: '.min.js'}))
        .pipe(gulp.dest('./prd/js'));
});

gulp.task('copy-img', function () {
    gulp.src('./dev/img')
        .pipe(gulp.dest('./prd/'))
});