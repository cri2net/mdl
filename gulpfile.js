var gulp = require('gulp');
var less = require('gulp-less');
var gutil = require('gulp-util');
var connect = require('gulp-connect');
var clean = require('gulp-clean');
var uglify = require('gulp-uglify');


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
        .pipe(connect.reload())
});

/* TASK: Create webserver for LiveReload */
gulp.task('connect', function () {
    connect.server({
        root: ['public'],
        host: 'gioc.dev',
        port: 8009,
        livereload: true
    });
});

/* TASK: Moving PHP & HTML files to public directory */
gulp.task('webpages', function(){
    gulp.src(WEBPAGES_MASK)
        .pipe(gulp.dest('./public/'))
        .pipe(connect.reload());
});

/* TASK: Moving JS files to public/js directory */
gulp.task('js', function(){
    gulp.src(JS_MASK)
        .pipe(uglify())
        .pipe(gulp.dest('./public/'))
        .pipe(connect.reload())
});

/* TASK: Moving CSS files to public directory */
gulp.task('css', function(){
    gulp.src(CSS_MASK)
        .pipe(uglify())
        .pipe(gulp.dest('./public/'))
        .pipe(connect.reload())
});

/* TASK: Moving images (only external, not CSS-encoded) to public/pic directory */
gulp.task('images', function(){
    gulp.src(IMAGES_MASK)
        .pipe(gulp.dest('./public/'))
        .pipe(connect.reload())
})

/* TASK: Moving fonts to public directory */
gulp.task('fonts', function(){
    gulp.src(FONTS_MASK)
        .pipe(gulp.dest('./public'))
        .pipe(connect.reload())
})

/* TASK: Watching changes in all-source & images */
gulp.task('watch', function(){
    gulp.watch(['./src/**/*.less'], ['less'] );
    gulp.watch(WEBPAGES_MASK, ['webpages'] );
    gulp.watch(IMAGES_MASK, ['images'] );
    gulp.watch(CSS_ENCODED_IMAGES_MASK, ['less'] );
    gulp.watch('./src/**/*.js', ['js'] );
});

gulp.task('cleanup', function(){
    gulp.src(PUBLIC_CLEANUP_MASK, { read:false })
        .pipe(clean());
});

/* DEFAULT TASK, running needed tasks */
gulp.task('default', ['connect', 'webpages', 'less', 'images', 'fonts', 'js', 'css', 'watch']);