var gulp       = require('gulp'),
    less       = require('gulp-less'),
    gutil      = require('gulp-util'),
    connect    = require('gulp-connect'),
    clean      = require('gulp-clean'),
    uglify     = require('gulp-uglify'),
    livereload = require('gulp-livereload');
    // watch      = require('gulp-watch');

var WEBPAGES_MASK = [
    './src/**/.htaccess',
    './src/manifest.json',
    './src/browserconfig.xml',
    './src/robots.txt',
    './src/**/{*.php,*.html}',
     './src/{script,config,lib,auth,splash,protected}/**/*.*',
    '!./src/protected/**/{*.css,*.js}',
    '!./src/protected/**/db.conf.sample.php',
    '!./src/protected/vendor/**/*.xml.dist',
    '!./src/protected/vendor/**/composer.{json,lock}',
    '!./src/protected/vendor/**/*.md',
    '!./src/protected/vendor/**/{tests,Tests,test,docs,examples}/**/*.*'
];

var IMAGES_MASK = ['./src/{pic,images}/**/{*.png,*.jpg,*.jpeg,*.gif,*.ico}', '!./src/pic/**/__*.*'];
var CSS_ENCODED_IMAGES_MASK = ['./src/{pic,images}/__*.*'];
var FONTS_MASK = ['./src/**/{*.ttf,*.eot,*.svg,*.woff}'];
var PUBLIC_CLEANUP_MASK = ['./public/**/{*.*,.*,*}'];
var JS_MASK = ['./src/{js,css}/**/*.js'];
var CSS_MASK = ['./src/{js,css}/**/*.css'];


/* TASK: Compile LESS files in single CSS */
gulp.task('less', function () {
    gulp.src('./src/less/style.less')
        .pipe(less({
            compress: false
        })
        .on('error', gutil.log)
        .on('error', gutil.beep))
        .pipe(gulp.dest('./public/css/'))
});

gulp.task('less_site', function () {
    gulp.src('./src/less/style_site.less')
        .pipe(less({
            compress: true
        })
        .on('error', gutil.log)
        .on('error', gutil.beep))
        .pipe(gulp.dest('./public/css/'))
});

/* TASK: Compile LESS files in no-compress CSS file */
gulp.task('less_nocompress', function () {
    gulp.src('./src/less/no-compress.less')
        .pipe(less({}))
        .pipe(gulp.dest('./public/css/'))
});

/* TASK: Create webserver for LiveReload */
gulp.task('connect', function () {
    connect.server({
        root: ['public'],
        host: 'localhost',
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
        // .pipe(uglify())
        .pipe(gulp.dest('./public/'))
});

/* TASK: Moving CSS files to public directory */
gulp.task('css', function(){
    gulp.src(CSS_MASK)
        //.pipe(uglify())
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

gulp.task('watch', function(){
    gulp.watch(WEBPAGES_MASK, ['webpages']);
    gulp.watch(['./src/less/**/*.less'], ['less']);
    gulp.watch(IMAGES_MASK, ['images']);
    gulp.watch('./src/css/*.css', ['css']);
    gulp.watch(JS_MASK, ['js']);
});


gulp.task('cleanup', function(){
    gulp.src(PUBLIC_CLEANUP_MASK, { read:false })
        .pipe(clean());
});

/* DEFAULT TASK, running needed tasks */
gulp.task('default', ['connect', 'webpages', 'less', 'images', 'fonts', 'js', 'css', 'watch']);
