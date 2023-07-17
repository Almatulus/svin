let gulp = require('gulp'),
  concat = require('gulp-concat'),
  uglify = require('gulp-uglify'),
  cleanCSS = require('gulp-clean-css'),
  sourcemaps = require('gulp-sourcemaps'),
  watch = require('gulp-watch'),
    babel = require('gulp-babel')/*,
  sass = require('gulp-sass')*/;

const paths = {
  dest_js: 'frontend/web/build/js/',
  dest_css: 'frontend/web/build/css/',
  dest_bootstrap_fonts: 'frontend/web/build/fonts/',
  dest_fa_fonts: 'frontend/web/build/webfonts/',
  vendor_scripts: [
    'vendor/bower/jquery-ui/jquery-ui.js',
    'vendor/bower/bootbox.js/bootbox.js',
    'vendor/bower/qtip2/jquery.qtip.min.js',
    'vendor/bower/jquery.fancytree/dist/jquery.fancytree-all.js',
    'vendor/bower/jquery-simplecolorpicker/jquery.simplecolorpicker.js',
    'vendor/bower/moment/moment.js',
    'vendor/bower/jGrowl/jquery.jgrowl.min.js',
    'vendor/bower/d3/d3.js',
    'vendor/bower/c3/c3.js',
    'vendor/bower/fullcalendar/dist/fullcalendar.js',
    'vendor/bower/fullcalendar/dist/locale/ru.js', // нужна локализация
    'vendor/bower/fullcalendar-scheduler/dist/scheduler.js',
    'vendor/bower/offline/offline.min.js',
    'vendor/bower/awesomplete/awesomplete.min.js',
  ],
  vendor_styles: [
  ],
  app: [
    'static/js/staff.js',
    'static/js/site.js',
    'static/js/file.js',
    'static/js/medexam.js',
    'static/js/tableHeadFixer.js',
    'static/js/schedule.js',
    'static/js/timetable.js',
    'static/js/client.js',
    'static/js/customer.js',
    'static/js/customer-loyalty.js',
    'static/js/order.js',
    'static/js/import.js',
    'static/js/med-card-services.js',
    'static/js/customer-debt.js',
    'static/js/voice-recognition.js',
    'static/js/image_upload.js',
    'static/js/timetable-usage.js',
    'static/js/warehouse.js',
    'static/js/source.js',
    'static/js/product.js',
  ],
  styles: [
    'static/css/versum/versum.css',
    'vendor/bower/components-font-awesome/css/fontawesome-all.css',
    'vendor/bower/qtip2/jquery.qtip.css',
    'vendor/bower/offline/themes/offline-theme-slide.css',
    'vendor/bower/awesomplete/awesomplete.css',
    'static/css/offline-russian-content.css',
    'static/css/versum/schedule.css',
    'static/css/customer.css',
    'static/css/site.css',
    'static/css/adminlte-corrections.css',
    'static/css/timetable-usage.css',
  ],
  auth_scripts: [
    'vendor/bower/bootbox.js/bootbox.js',
    'static/js/auth.js',
  ],
  auth_styles: [
    'static/css/auth.css',
  ],
  fa_fonts: [
    'vendor/bower/components-font-awesome/webfonts/*',
  ],
  bootstrap_fonts: [
    'vendor/bower/bootstrap/dist/fonts/**/*',
  ],
};

gulp.task('minify-vendor-js', () => gulp.src(paths.vendor_scripts)
  .pipe(sourcemaps.init())
  .pipe(uglify())
  .pipe(concat('vendor.min.js'))
  .pipe(sourcemaps.write())
  .pipe(gulp.dest(paths.dest_js))
  .on('error', (err) => {
    console.error('Error in compress task', err.toString());
  }));

gulp.task('minify-vendor-css', () => gulp.src(paths.vendor_styles)
  .pipe(cleanCSS({ processImport: false, keepBreaks: false }))
  .pipe(concat('vendor.min.css'))
  .pipe(gulp.dest(paths.dest_css))
  .on('error', (err) => {
    console.error('Error in compress task', err.toString());
  }));

gulp.task('minify-js', () => gulp.src(paths.app)
  .pipe(babel({ presets: ['env'] }))
  .pipe(sourcemaps.init())
  .pipe(uglify())
  .pipe(concat('all.min.js'))
  .pipe(sourcemaps.write())
  .pipe(gulp.dest(paths.dest_js))
  .on('error', (err) => {
    console.error('Error in compress task', err.toString());
  }));

gulp.task('minify-css', () => gulp.src(paths.styles)
  .pipe(cleanCSS({ processImport: false, keepBreaks: false }))
  .pipe(concat('all.min.css'))
  .pipe(gulp.dest(paths.dest_css))
  .on('error', (err) => {
    console.error('Error in compress task', err.toString());
  }));

gulp.task('auth-js', () => gulp.src(paths.auth_scripts)
  .pipe(sourcemaps.init())
  .pipe(uglify())
  .pipe(concat('auth.min.js'))
  .pipe(sourcemaps.write())
  .pipe(gulp.dest(paths.dest_js))
  .on('error', (err) => {
    console.error('Error in compress task', err.toString());
  }));

gulp.task('auth-css', () => gulp.src(paths.auth_styles)
  .pipe(cleanCSS({ processImport: false, keepBreaks: false }))
  .pipe(concat('auth.min.css'))
  .pipe(gulp.dest(paths.dest_css))
  .on('error', (err) => {
    console.error('Error in compress task', err.toString());
  }));

gulp.task('fa_fonts', () => gulp.src(paths.fa_fonts)
  .pipe(gulp.dest(paths.dest_fa_fonts))
  .on('error', (err) => {
    console.error('Error in compress task', err.toString());
  }));

gulp.task('bootstrap_fonts', () => gulp.src(paths.bootstrap_fonts)
  .pipe(gulp.dest(paths.dest_bootstrap_fonts))
  .on('error', (err) => {
      console.error('Error in compress task', err.toString());
  }));

gulp.task('sass', () => gulp.src('web/sass/**/*.scss')
  .pipe(sass().on('error', sass.logError))
  .pipe(gulp.dest(paths.dest_css))
  .on('error', (err) => {
    console.error('Error in compress task', err.toString());
  }));

gulp.task('default', [
  'minify-js',
  'minify-css',
  'auth-js',
  'auth-css',
]);

gulp.task('all', [
  'minify-vendor-js',
  // 'minify-vendor-css',
  'minify-js',
  'minify-css',
  'fa_fonts',
  'bootstrap_fonts',
  'auth-js',
  'auth-css',
]);

gulp.task('watch', () => gulp.watch([
  paths.javascript,
  paths.styles,
], ['minify-js', 'minify-css']));
