// ## Globals
var argv              = require('minimist')(process.argv.slice(2));
var async             = require('async');
var browserSync       = require('browser-sync').create();
var changed           = require('gulp-changed');
var concat            = require('gulp-concat');
var consolidate       = require('gulp-consolidate');
var eslint            = require('gulp-eslint');
var gulp              = require('gulp');
var gulpif            = require('gulp-if');
var iconfont          = require('gulp-iconfont');
var imagemin          = require('gulp-imagemin');
var imageminPngquant  = require('imagemin-pngquant');
var jade              = require('gulp-jade');
var jadePhp           = require('gulp-jade-php');
var jeet              = require('jeet');
var koutoSwiss        = require('kouto-swiss');
var lazypipe          = require('lazypipe');
var less              = require('gulp-less');
var merge             = require('merge-stream');
var ngAnnotate        = require('gulp-ng-annotate');
var cssNano           = require('gulp-cssnano');
var plumber           = require('gulp-plumber');
var rev               = require('gulp-rev');
var rupture           = require('rupture');
var runSequence       = require('run-sequence');
var runTimestamp      = Math.round(Date.now()/1000);
var sass              = require('gulp-sass');
var stylus            = require('gulp-stylus');
var sourcemaps        = require('gulp-sourcemaps');
var uglify            = require('gulp-uglify');

// See https://github.com/austinpray/asset-builder
var manifest = require('asset-builder')('./src/assets/manifest.json');

// `path` - Paths to base asset directories. With trailing slashes.
// - `path.source` - Path to the source files. Default: `assets/`
// - `path.dist` - Path to the build directory. Default: `dist/`
var path = manifest.paths;

// `config` - Store arbitrary configuration values here.
var config = manifest.config || {};

// `globs` - These ultimately end up in their respective `gulp.src`.
// - `globs.js` - Array of asset-builder JS dependency objects. Example:
//   ```
//   {type: 'js', name: 'main.js', globs: []}
//   ```
// - `globs.css` - Array of asset-builder CSS dependency objects. Example:
//   ```
//   {type: 'css', name: 'main.css', globs: []}
//   ```
// - `globs.fonts` - Array of font path globs.
// - `globs.images` - Array of image path globs.
// - `globs.bower` - Array of all the main Bower files.
var globs = manifest.globs;

// `project` - paths to first-party assets.
// - `project.js` - Array of first-party JS assets.
// - `project.css` - Array of first-party CSS assets.
var project = manifest.getProjectGlobs();

// CLI options
var enabled = {
  // Enable static asset revisioning when `--production`
  rev: argv.production,
  // Disable source maps when `--production`
  maps: !argv.production,
  // Fail styles task on error when `--production`
  failStyleTask: argv.production,
  // Fail due to ESLint warnings only when `--production`
  failESLint: argv.production,
  // Strip debug statments from javascript when `--production`
  stripJSDebug: argv.production
};

// Path to the compiled assets manifest in the dist directory
var revManifest = path.dist + 'assets.json';

// ## Reusable Pipelines
// See https://github.com/OverZealous/lazypipe

// ### CSS processing pipeline
// Example
// ```
// gulp.src(cssFiles)
//   .pipe(cssTasks('main.css')
//   .pipe(gulp.dest(path.dist + 'styles'))
// ```
var cssTasks = function(filename) {
  return lazypipe()
    .pipe(function() {
      return gulpif(!enabled.failStyleTask, plumber());
    })
    .pipe(function() {
      return gulpif(enabled.maps, sourcemaps.init());
    })
    .pipe(function() {
      return gulpif('*.styl', stylus());
    })
    .pipe(function() {
      return gulpif('*.less', less());
    })
    .pipe(function() {
      return gulpif('*.scss', sass({
        outputStyle: 'nested', // libsass doesn't support expanded yet
        precision: 10,
        includePaths: ['.'],
        errLogToConsole: !enabled.failStyleTask
      }));
    })
    .pipe(concat, filename)
    .pipe(cssNano, {
      safe: true
    })
    .pipe(function() {
      return gulpif(enabled.rev, rev());
    })
    .pipe(function() {
      return gulpif(enabled.maps, sourcemaps.write('.', {
        sourceRoot: 'src/assets/styles/'
      }));
    })();
};

// ### JS processing pipeline
// Example
// ```
// gulp.src(jsFiles)
//   .pipe(jsTasks('main.js')
//   .pipe(gulp.dest(path.dist + 'scripts'))
// ```
var jsTasks = function(filename) {
  return lazypipe()
    .pipe(function() {
      return gulpif(enabled.maps, sourcemaps.init());
    })
    .pipe(concat, filename)
    .pipe(uglify, {
      mangle: !enabled.maps,
      compress: {
        'drop_debugger': enabled.stripJSDebug
      }
    })
    .pipe(function() {
      return gulpif(enabled.rev, rev());
    })
    .pipe(function() {
      return gulpif(enabled.maps, sourcemaps.write('.', {
        sourceRoot: 'src/assets/scripts/'
      }));
    })();
};

// ### Write to rev manifest
// If there are any revved files then write them to the rev manifest.
// See https://github.com/sindresorhus/gulp-rev
var writeToManifest = function(directory) {
  return lazypipe()
    .pipe(gulp.dest, path.dist + directory)
    .pipe(browserSync.stream, {match: '**/*.{js,css}'})
    .pipe(rev.manifest, revManifest, {
      base: path.dist,
      merge: true
    })
    .pipe(gulp.dest, path.dist)();
};

// ## Gulp tasks
// Run `gulp -T` for a task summary

// ### Templates
// `gulp templates` - Compiles jade-php templates
// By default this task will only log a warning if a precompiler error is
// raised. If the `--production` flag is set: this task will fail outright.
gulp.task('templates', function() {
  // PHP Templates
  gulp.src([
    './src/views/**/*.jade',
    // Exclude following patterns:
    '!./src/views/partials/**/*.jade', // exclude `partials` directories
    '!./src/views/**/includes/**/*.jade', // exclude `includes` directories
    '!./src/views/**/ng/**/*', // exclude 'ng' directories (angluar templates)
  ])
    .pipe(plumber())
    .pipe(jadePhp({
      "pretty": true
    }))
    .pipe(plumber.stop())
    .pipe(gulp.dest('./templates'));

  // Angular apps templates
  gulp.src([
    './src/views/**/ng/**/*.jade', // only from ng directories
    '!./src/views/**/ng/includes/**/*.jade', // exclude `includes` directories
    '!./src/views/**/ng/**/includes/**/*.jade', // exclude `includes` directories
  ])
    .pipe(jade({
      pretty: true
    }))
    .pipe(gulp.dest('./templates'))
});

// ### Styles
// `gulp styles` - Compiles, combines, and optimizes Bower CSS and project CSS.
// By default this task will only log a warning if a precompiler error is
// raised. If the `--production` flag is set: this task will fail outright.
gulp.task('styles', ['wiredep'], function() {
  var merged = merge();
  manifest.forEachDependency('css', function(dep) {
    var cssTasksInstance = cssTasks(dep.name);
    if (!enabled.failStyleTask) {
      cssTasksInstance.on('error', function(err) {
        console.$log.log(err.message);
        this.emit('end');
      });
    }
    merged.add(gulp.src(dep.globs, {base: 'styles'})
      .pipe(stylus({ use: [ koutoSwiss(), jeet(), rupture() ] }))
      .pipe(cssTasksInstance));
  });
  return merged
    .pipe(writeToManifest('styles'));
});

// ### Scripts
// `gulp scripts` - Runs ESLint then compiles, combines, and optimizes Bower JS
// and project JS.
gulp.task('scripts', function() {
  var merged = merge();
  manifest.forEachDependency('js', function(dep) {
    merged.add(
      gulp.src(dep.globs, {base: 'scripts'})
        .pipe(jsTasks(dep.name))
    );
  });
  return merged
    .pipe(writeToManifest('scripts'));
});

// ### IconFont
// `gulp iconfont` - Converts svg icons into font, generates .styl css objects
// and components classes. For svg icons optimization options
// see: https://www.npmjs.com/package/gulp-iconfont
gulp.task('iconfont', function(done){

  var fontName        = 'ReBeIconFont';
  var fontPath        = '../fonts/';
  var objectsClass    = 'o-icon';
  var componentsClass = 'c-icon';

  var iconStream = gulp.src(['./src/assets/icons/*.svg'])
    .pipe(iconfont({
      fontName: fontName,
      formats: ['ttf', 'eot', 'woff'],
      timestamp: Math.round(Date.now() / 1000),
      normalize: true,  /* fix possible icon height difference */
      fontHeight: 1001  /* fix possible icon convertion issues */
     }));
    async.parallel([
      // Generate icons CSS objects classes
      function GenerateIconCssObjects(cb) {
        iconStream.on('glyphs', function(glyphs, options) {
          gulp.src('./src/assets/styles/templates/_icon.styl') /* objects template */
            .pipe(consolidate('lodash', {
              glyphs: glyphs,
              fontName: fontName,
              fontPath: fontPath,
              objectsClass: objectsClass
            }))
            .pipe(gulp.dest('./src/assets/styles/objects/'))
            .on('finish', cb);
        });
      },
      // Generate icons CSS components classes
      function GenerateIconCssComponents(cb) {
        iconStream.on('glyphs', function(glyphs, options) {
          gulp.src('./src/assets/styles/templates/_icons.styl') /* components template */
            .pipe(consolidate('lodash', {
              glyphs: glyphs,
              objectsClass: objectsClass,
              componentsClass: componentsClass
            }))
            .pipe(gulp.dest('./src/assets/styles/components/common-ui'))
            .on('finish', cb);
        });
      },
      // Output web fonts
      function outputFonts(cb) {
        iconStream
          .pipe(gulp.dest('./dist/fonts/'))
          .on('finish', cb);
      }
    ], done);
});

// ### Images
// `gulp images` - Run lossless compression on all the images.
gulp.task('images', function() {
  return gulp.src(globs.images)
    .pipe(imagemin({
      progressive: true,
      interlaced: true,
      use: [imageminPngquant()],
      svgoPlugins: [{removeUnknownsAndDefaults: false}, {cleanupIDs: false}]
    }))
    .pipe(gulp.dest(path.dist + 'images'))
    .pipe(browserSync.stream());
});

// ### ESLint
// `gulp lint` - Lints configuration JSON and project JS.
gulp.task('lint', function() {
  return gulp.src([
    'gulpfile.js'
  ].concat(project.js))
    .pipe(eslint())
    .pipe(eslint.format())
    .pipe(gulpif(enabled.failESLint, eslint.failAfterError()));
});

// ### Clean
// `gulp clean` - Deletes the build folder entirely.
gulp.task('clean', require('del').bind(null, [path.dist]));

// ### Watch
// `gulp watch` - Use BrowserSync to proxy your dev server and synchronize code
// changes across devices. Specify the hostname of your dev server at
// `manifest.config.devUrl`. When a modification is made to an asset, run the
// build step for that asset and inject the changes into the page.
// See: http://www.browsersync.io
gulp.task('watch', function() {
  browserSync.init({
    files: ['{src,templates}/**/*.php', 'src/views/**/*.html'],
    proxy: config.devUrl,
    snippetOptions: {
      whitelist: ['/wp-admin/admin-ajax.php'],
      blacklist: ['/wp-admin/**']
    }
  });
  gulp.watch(['src/views/**/*'], ['templates']);
  gulp.watch([path.source + 'icons/**/*'], ['iconfont', 'styles']);
  gulp.watch([path.source + 'styles/**/*'], ['styles']);
  gulp.watch([path.source + 'scripts/**/*'], ['scripts']);
  gulp.watch([path.source + 'images/**/*'], ['images']);
  gulp.watch(['bower.json', 'src/assets/manifest.json'], ['build']);
});

// ### Build
// `gulp build` - Run all the build tasks but don't clean up beforehand.
// Generally you should be running `gulp` instead of `gulp build`.
gulp.task('build', function(callback) {
  runSequence(
    'templates',
    'styles',
    'scripts',
    'iconfont',
    'images',
    callback
  );
});

// ### Wiredep
// `gulp wiredep` - Automatically inject Less and Sass Bower dependencies. See
// https://github.com/taptapship/wiredep
gulp.task('wiredep', function() {
  var wiredep = require('wiredep').stream;
  return gulp.src(project.css)
    .pipe(wiredep())
    .pipe(changed(path.source + 'styles', {
      hasChanged: changed.compareSha1Digest
    }))
    .pipe(gulp.dest(path.source + 'styles'));
});

// ### Gulp
// `gulp` - Run a complete build. To compile for production run `gulp --production`.
gulp.task('default', ['clean'], function() {
  gulp.start('build');
});
