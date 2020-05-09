var gulp    = require('gulp'),
    uglify  = require('gulp-uglify'),
    connect = require('gulp-connect'),
    concat  = require('gulp-concat'),
    rename  = require('gulp-rename'),
    sass    = require('gulp-sass');

var WEBPAGES_MASK = [
    './src/**/.htaccess',
    './src/**/{*.php,*.html}',
     './src/{script,config,lib,auth,splash,protected}/**/*.*',
    '!./src/protected/**/{*.css,*.js}',
    '!./src/protected/**/db.conf.sample.php',
    '!./src/protected/vendor/**/*.xml.dist',
    '!./src/protected/vendor/**/composer.{json,lock}',
    '!./src/protected/vendor/**/*.md',
    '!./src/protected/vendor/**/{tests,Tests,test,docs,examples}/**/*.*'
];

var IMAGES_MASK = ['./src/{pic,images}/**/{*.png,*.jpg,*.jpeg,*.gif,*.ico,*.webp}', '!./src/{pic,images}/**/__*.*'];
var CSS_ENCODED_IMAGES_MASK = ['./src/{pic,images}/__*.*'];
var FONTS_MASK = ['./src/**/*.{ttf,svg,woff,woff2,eot,otf}'];
var JS_MASK = ['./src/{js,css}/**/*.js'];
var PUBLIC_CLEANUP_MASK = ['./public/**/{*.*,.*,*}'];
var CSS_MASK = ['./src/{js,css}/**/*.css'];


/* TASK: Moving PHP & HTML files to public directory */
gulp.task('webpages', function(){
    return gulp.src(WEBPAGES_MASK)
        .pipe(gulp.dest('./public'))
        .pipe(connect.reload())
        .on('error', function(err){
            console.error(err);
        })
});

sass.compiler = require('node-sass');

gulp.task('sass', function () {
    return gulp.src('./src/scss/**/*.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('./public/assets/css'))
        .pipe(connect.reload())
});


/* TASK: Moving JS files to public/css directory */
gulp.task('css', function(){
    return gulp.src('./src/css/*.css')
        .pipe(gulp.dest('./public/assets/css/'))
        .pipe(connect.reload())
});

/* TASK: Moving JS files to public/js directory */
gulp.task('js', function(){
    return gulp.src(['./src/js/**/*.js', '!./src/js/**/scripts.js'])
        .pipe(uglify())
        .pipe(gulp.dest('./public/assets/js/'))
        .pipe(connect.reload())
        .on('error', function(err) {
            console.error(err);
        })
});

var jsFiles = [
        './src/js/vendors/jquery.min.js',
        './src/js/vendors/jquery-ui.1.10.4.min.js',
        './src/js/vendors/*.js'],
    jsDest = './public/assets/js/';

gulp.task('js_all', function() {  
    return gulp.src(jsFiles)
        .pipe(concat('plugins.js'))
        .pipe(gulp.dest(jsDest))
        .pipe(rename('plugins.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest(jsDest));
});

/* TASK: Moving JS files to public/js directory */
gulp.task('js_script', function(){
    return gulp.src(['./src/js/**/scripts.js', './src/js/main.js'])
            .pipe(uglify())
            .pipe(gulp.dest('./public/assets/js/'))
            .pipe(connect.reload())
            .on('error', function(err) {
                console.error(err);
            })
});

/* TASK: Moving images (only external, not CSS-encoded) to public/images directory */
gulp.task('images', function(){
    return gulp.src(IMAGES_MASK)
        .pipe(gulp.dest('./public/assets'))
        .pipe(connect.reload())
})

/* TASK: Moving fonts to public/style/fonts directory */
gulp.task('fonts', function(){
    return gulp.src(FONTS_MASK)
        .pipe(gulp.dest('./public/assets'))
        .pipe(connect.reload())
})

/* TASK: Create webserver for LiveReload */
gulp.task('connect', function (callback) {
    connect.server({
        root: ['public'],
        host: '127.0.0.1',
        port: 8013,
        livereload: true
    });
    callback();
});

/* TASK: Watching changes in all-source & images */
gulp.task('watch', function(callback){
    gulp.watch(WEBPAGES_MASK, gulp.series('webpages'));
    gulp.watch('./src/scss/**/*.scss', gulp.series('sass'));
    gulp.watch(IMAGES_MASK, gulp.series('images'));
    gulp.watch('./src/css/*.css', gulp.series('css'));
    gulp.watch(JS_MASK, gulp.series('js_script'));

    callback();
});

/**
 * Wrap gulp streams into fail-safe function for better error reporting
 * Usage:
 * gulp.task('less', wrapPipe(function(success, error) {
 *   return gulp.src('less/*.less')
 *      .pipe(less().on('error', error))
 *      .pipe(gulp.dest('app/css'));
 * }));
 */ 
function wrapPipe(taskFn) {
    return function(done) {
        var onSuccess = function() {
            done();
        };
        var onError = function(err) {
            done(err);
        }
        var outStream = taskFn(onSuccess, onError);
        if (outStream && typeof outStream.on === 'function') {
            outStream.on('end', onSuccess);
        }
    }
}

/* DEFAULT TASK, running needed tasks */
gulp.task('default',  gulp.parallel('css', 'connect', 'sass', 'webpages', 'js_script', 'js_all', 'images', 'fonts', 'watch'));
