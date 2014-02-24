
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=80; */

/**
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

module.exports = function(grunt) {

  grunt.loadNpmTasks('grunt-bower-task');
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-contrib-compress');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-phpunit');

  var pkg = grunt.file.readJSON('package.json');

  grunt.initConfig({

    bower: {
      install: {
        options: { copy: false }
      }
    },

    clean: {
      bower: 'bower_components',
      pkg: 'pkg'
    },

    phpunit: {

      options: {
        bin: 'vendor/bin/phpunit',
        bootstrap: 'tests/phpunit/bootstrap.php',
        followOutput: true,
        colors: true
      },

      application: {
        dir: 'tests/'
      }

    },

    //concat: {},
    //uglify: {},

    compass: {

      dist: {
        options: {
          sassDir: 'views/shared/css/sass',
          cssDir: 'views/shared/css'
        }
      }

    },

    watch: {

      payload: {
        files: 'views/shared/css/sass/*.scss',
        tasks: 'compile:min'
      }

    },

    compress: {

      dist: {
        options: {
          archive: 'pkg/SolrSearch-'+pkg.version+'.zip'
        },
        dest: 'SolrSearch/',
        src: [

          '**',

          // GIT
          '!.git/**',

          // BOWER
          '!bower.json',
          '!bower_components/**',

          // NPM
          '!package.json',
          '!node_modules/**',

          // COMPOSER
          '!composer.json',
          '!composer.lock',
          '!vendor/**',

          // RUBY
          '!Gemfile',
          '!Gemfile.lock',

          // GRUNT
          '!.grunt/**',
          '!Gruntfile.js',

          // DIST
          '!pkg/**',

          // TESTS
          '!tests/**'

        ]
      }

    }

  });

  // Run application tests.
  grunt.registerTask('default', 'phpunit');

  // Build the application.
  grunt.registerTask('build', [
    'clean',
    'bower',
    'compile:min'
  ]);

  // Compile JS/CSS payloads.
  grunt.registerTask('compile', [
    //'concat',
    'compass'
  ]);

  // Minify JS/CSS payloads.
  grunt.registerTask('compile:min', [
    //'uglify',
    'compass'
  ]);

  // Spawn release package.
  grunt.registerTask('package', [
    'clean:pkg',
    'compile:min',
    'compress'
  ]);

};
