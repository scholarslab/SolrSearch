
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=80; */

/**
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

module.exports = function(grunt) {

  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-compress');
  grunt.loadNpmTasks('grunt-contrib-jasmine');
  grunt.loadNpmTasks('grunt-contrib-connect');
  grunt.loadNpmTasks('grunt-phpunit');

  grunt.initConfig({

    phpunit: {

      options: {
        bin: 'vendor/bin/phpunit',
        bootstrap: 'tests/bootstrap.php',
        followOutput: true,
        colors: true
      },

      application: {
        dir: 'tests/'
      }

    },

    concat: {

      results: {
        src: [
          'views/shared/javascripts/vendor/jquery.infinitescroll.js',
          'views/shared/javascripts/facets.js',
          'views/shared/javascripts/scroll.js'
        ],
        dest: 'views/shared/javascripts/payloads/results.js'
      }

    },

    uglify: {

      results: {
        src: '<%= concat.results.src %>',
        dest: '<%= concat.results.dest %>'
      }

    }

  });

  // Run application tests.
  grunt.registerTask('default', 'test');

  // Run PHPUnit suite.
  grunt.registerTask('test', 'phpunit');

};
