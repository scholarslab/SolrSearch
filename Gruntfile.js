
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=80; */

/**
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

module.exports = function(grunt) {

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

    }

  });

  // Run application tests.
  grunt.registerTask('default', 'test');

  // Run application tests.
  grunt.registerTask('test', 'phpunit');

};

