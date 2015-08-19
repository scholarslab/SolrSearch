
/**
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

module.exports = function(grunt) {

  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-contrib-compress');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-phpunit');

  var pkg = grunt.file.readJSON('package.json');

  grunt.initConfig({

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

    compass: {

      dist: {
        options: {
          sassDir: 'views/shared/css/sass',
          cssDir: 'views/shared/css',
          outputStyle: 'compressed',
          sourcemap: true
        }
      }

    },

    watch: {

      payload: {
        files: 'views/shared/css/sass/*.scss',
        tasks: 'build'
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

          // SASS
          '!.sass-cache/**',

          // DIST
          '!pkg/**',

          // TESTS
          '!tests/**',

          // Editor settings
          '!*.vim'

        ]
      }

    }

  });

  // Run application tests.
  grunt.registerTask('default', 'phpunit');

  // Build the application.
  grunt.registerTask('build', [
    'clean',
    'compass'
  ]);

  // Spawn release package.
  grunt.registerTask('package', [
    'build',
    'compress'
  ]);

};
