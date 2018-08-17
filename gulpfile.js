var gulp      = require('gulp'),
    uglify    = require('gulp-uglify'),
    gutil     = require('gulp-util'),
    clean     = require('gulp-clean'),
    connect   = require('gulp-connect'),
    livereload   = require('gulp-livereload'),
    concat = require('gulp-concat'),
    rename = require('gulp-rename'),    
    less      = require('gulp-less'),
    minifyCss = require('gulp-minify-css');

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

var IMAGES_MASK = ['./src/{pic,images}/**/{*.png,*.jpg,*.jpeg,*.gif,*.ico}', '!./src/{pic,images}/**/__*.*'];
var CSS_ENCODED_IMAGES_MASK = ['./src/{pic,images}/__*.*'];
var FONTS_MASK = ['./src/**/*.{ttf,svg,woff,woff2,eot,otf}'];
var JS_MASK = ['./src/{js,css}/**/*.js'];
var PUBLIC_CLEANUP_MASK = ['./public/**/{*.*,.*,*}'];
var CSS_MASK = ['./src/{js,css}/**/*.css'];



/* TASK: Moving PHP & HTML files to public directory */
gulp.task('webpages', function(){
    gulp.src(WEBPAGES_MASK)
        .pipe(gulp.dest('./public'))
        .pipe(connect.reload())

    .on('error', function(err){
       console.error('error: ' + err.message);
       console.error(err.stack);
    });
});

/* TASK: Compile LESS files in single CSS */
gulp.task('less', wrapPipe(function(success, error) {
    return gulp.src('./src/less/*.less')
    .pipe(
        less({compress: false})
        .on('error', error)
        .on('error', gutil.beep)
    )
     .pipe(gulp.dest('./public/assets/css'))
     .pipe(livereload());
}));

/* TASK: Compile LESS files in single CSS */
gulp.task('bootstrap', wrapPipe(function(success, error) {
    return gulp.src('./src/less/bootstrap/bootstrap.less')
    .pipe(
        less({compress: false})
        .on('error', error)
        .on('error', gutil.beep)
    )
     .pipe(gulp.dest('./public/assets/css'))
     .pipe(connect.reload());
}));
    
/* TASK: Moving CSS files to public directory */
/*
gulp.task('css', function(){
    gulp.src(CSS_MASK)
        .pipe(uglify())
        .pipe(gulp.dest('./public/'))
});
*/
/* TASK: Moving JS files to public/css directory */
gulp.task('css', function(){
    gulp.src('./src/css/*.css')
/*        .pipe(minifyCss({compatibility: 'ie8'}) .on('error', gutil.log))*/
        .pipe(gulp.dest('./public/assets/css/'))
        .pipe(connect.reload())
});

/* TASK: Moving JS files to public/js directory */
gulp.task('js', function(){
    try {
        gulp.src(['./src/js/**/*.js', '!./src/js/**/scripts.js'])
            .pipe(uglify({
                preserveComments:'license'
            }))
            .pipe(gulp.dest('./public/assets/js/'))
            .pipe(connect.reload())
            .on('error', gutil.log)
            .on('error', gutil.beep)
    } catch (e) {
       console.error('error: ' + e.message);
       console.error(e.stack);
    }
});

var jsFiles = [
        './src/js/vendors/jquery.min.js',
        './src/js/vendors/jquery-ui.js',
        './src/js/vendors/*.js'],
    jsDest = './public/assets/js/';

gulp.task('js_all', function() {  
    return gulp.src(jsFiles)
        .pipe(concat('plugins.js'))
        .pipe(gulp.dest(jsDest))
        .pipe(rename('plugins.min.js'))

        .pipe(uglify({
            preserveComments:'license'
        }))

        .pipe(gulp.dest(jsDest));
});

/* TASK: Moving JS files to public/js directory */
gulp.task('js_script', function(){
    try {
        gulp.src(['./src/js/**/scripts.js', './src/js/main.js', './src/js/modernizr-2.6.2.min.js'])
            .pipe(gulp.dest('./public/assets/js/'))
            .pipe(connect.reload())
            .on('error', gutil.log)
            .on('error', gutil.beep)
    } catch (e) {
       console.error('error: ' + e.message);
       console.error(e.stack);
    }
});


/* TASK: Moving images (only external, not CSS-encoded) to public/images directory */
gulp.task('images', function(){
    gulp.src(IMAGES_MASK)
        .pipe(gulp.dest('./public/assets'))
        .pipe(connect.reload())
})

/* TASK: Moving fonts to public/style/fonts directory */
gulp.task('fonts', function(){
    gulp.src(FONTS_MASK)
        .pipe(gulp.dest('./public/assets'))
        .pipe(connect.reload())
})

/* TASK: Create webserver for LiveReload */
gulp.task('connect', function () {
    connect.server({
        root: ['public'],
        host: 'localhost',
        port: 8013,
        livereload: true
    });
});

/* TASK: Watching changes in all-source & images */
gulp.task('watch', function(){

    livereload.listen();    
    gulp.watch(WEBPAGES_MASK, ['webpages']);
    gulp.watch(['./src/less/**/*.less'], ['less']);
    gulp.watch(IMAGES_MASK, ['images']);
    gulp.watch('./src/css/*.css', ['css']);
    gulp.watch(JS_MASK, ['js_script']);
});

gulp.task('cleanup', function(){
// ищу, почему все падает    
//    gulp.src(PUBLIC_CLEANUP_MASK, { read:false })
//        .pipe(clean());
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
    if(outStream && typeof outStream.on === 'function') {
      outStream.on('end', onSuccess);
    }
  }
}

/* DEFAULT TASK, running needed tasks */
//gulp.task('default', ['connect', 'less', 'webpages', 'js', 'css', 'fonts', 'images', 'watch']);
//gulp.task('default', ['css', 'less', 'webpages', 'js', 'js_all', 'js_script', 'images', 'fonts', 'watch']);
//gulp.task('default', ['css', 'less', 'webpages', 'js']);
gulp.task('default', ['css', 'less', 'webpages', 'js_script', 'js_all', 'images', 'fonts', 'watch']);

