var gulp       = require('gulp'),
    less       = require('gulp-less'),
    gutil      = require('gulp-util'),
    connect    = require('gulp-connect'),
    clean      = require('gulp-clean'),
    uglify     = require('gulp-uglify'),
    livereload = require('gulp-livereload');
    watch      = require('gulp-watch');

paths = {
    webpages : {
    }
}

var WEBPAGES_MASK = [
    './src/**/.htaccess',
    './src/robots.txt',
    './src/**/{*.php,*.html}',
     './src/{script,config,lib,auth,splash,protected}/**/*.*',
    '!./src/protected/**/{*.css,*.js}'
];

var IMAGES_MASK = ['./src/{pic,images}/**/{*.png,*.jpg,*.gif,*.ico}', '!./src/pic/**/__*.*'];
var CSS_ENCODED_IMAGES_MASK = ['./src/{pic,images}/__*.*'];
var FONTS_MASK = ['./src/**/{*.ttf,*.eot,*.svg,*.woff}'];
var PUBLIC_CLEANUP_MASK = ['./public/**/{*.*,.*,*}'];
var JS_MASK = ['./src/{js,css}/**/*.js'];
var CSS_MASK = ['./src/{js,css}/**/*.css'];


/* TASK: Compile LESS files in single CSS */
gulp.task('less', function () {
    gulp.src('./src/less/style.less')
        .pipe(less({
            compress: true
        })
        .on('error', gutil.log)
        .on('error', gutil.beep))
        .pipe(gulp.dest('./public/style/'))
});

/* TASK: Create webserver for LiveReload */
gulp.task('connect', function () {
    connect.server({
        root: ['public'],
        host: 'gioc.dev',
        port: 8009
    });
});

/* TASK: Moving PHP & HTML files to public directory */
gulp.task('webpages', function(){
    gulp.src(WEBPAGES_MASK)
        .pipe(gulp.dest('./public/'))
});

/* TASK: Moving JS files to public/js directory */
gulp.task('js', function(){
    gulp.src(JS_MASK)
        .pipe(uglify())
        .pipe(gulp.dest('./public/'))
});

/* TASK: Moving CSS files to public directory */
gulp.task('css', function(){
    gulp.src(CSS_MASK)
        .pipe(uglify())
        .pipe(gulp.dest('./public/'))
});

/* TASK: Moving images (only external, not CSS-encoded) to public/pic directory */
gulp.task('images', function(){
    gulp.src(IMAGES_MASK)
        .pipe(gulp.dest('./public/'))
})

/* TASK: Moving fonts to public directory */
gulp.task('fonts', function(){
    gulp.src(FONTS_MASK)
        .pipe(gulp.dest('./public'))
})

/* TASK: Watching changes in all-source & images */
gulp.task('watch', function(){
    livereload.listen();

    watch(['./src/**/*.less'], ['less']).on('change', stackReload);
    watch(WEBPAGES_MASK, ['webpages']).on('change', stackReload);
    watch(IMAGES_MASK, ['images']).on('change', stackReload);
    watch(CSS_ENCODED_IMAGES_MASK, ['less'] ).on('change', stackReload);
    watch('./src/**/*.js', ['js']).on('change', stackReload);


    var timer = null; // a timeout variable

    // actual reload function
    function stackReload() {
        var reload_args = arguments;

        // Stop timeout function to run livereload if this function is ran within the last 250ms
        if (timer) {
            clearTimeout(timer);
        }

        // Check if any gulp task is still running
        if (!gulp.isRunning) {
            timer = setTimeout(function() {
                livereload.changed.apply(null, reload_args);
            }, 350);
        }
    }
});

gulp.task('cleanup', function(){
    gulp.src(PUBLIC_CLEANUP_MASK, { read:false })
        .pipe(clean());
});

/* DEFAULT TASK, running needed tasks */
gulp.task('default', ['connect', 'webpages', 'less', 'images', 'fonts', 'js', 'css', 'watch']);
