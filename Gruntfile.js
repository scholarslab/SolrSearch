
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
  grunt.loadNpmTasks('grunt-phpunit');

  var pkg = grunt.file.readJSON('package.json');

  grunt.initConfig({

    bower: {
      install: {
        options: { copy: false }
      }
    },

    clean: {
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

    concat: {

      fields: {
        src: [
          'bower_components/textinplace/dist/jquery.textinplace.js',
          'views/admin/javascripts/accordion.js',
          'views/admin/javascripts/select.js'
        ],
        dest: 'views/admin/javascripts/payloads/fields.js'
      },

      results: {
        src: [
          'bower_components/infinitescroll/jquery.infinitescroll.js',
          'views/shared/javascripts/facets.js',
          'views/shared/javascripts/scroll.js'
        ],
        dest: 'views/shared/javascripts/payloads/results.js'
      }

    },

    uglify: {

      fields: {
        src: '<%= concat.fields.src %>',
        dest: '<%= concat.fields.dest %>'
      },

      results: {
        src: '<%= concat.results.src %>',
        dest: '<%= concat.results.dest %>'
      }

    },

    compass: {

      dist: {
        sassDir: '_sass',
        cssDir: 'views/shared/css'
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

  // Spawn release package.
  grunt.registerTask('package', ['clean:pkg', 'uglify', 'compress']);

};
