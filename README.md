About SolrSearch 
----------

**SolrSearch** is an Omeka plugin that allows you to leverage the powerful
[Solr][solr] search engine within Omeka. Not only does Solr provide robust,
configurable, full-text indexing, it also allows you a flexible
interface to configure how your users discover the content in your
Omeka application. 

## Requirements

**SolrSearch** relies on access to a [Solr 3.5+][solr] server to
maintain the search indexes. Installation of this software is covered in
the [Solr Documentation][2]. 

### Configuration
Once Solr is up-and-running, you will need to tell Solr about the the
Omeka SolrSearch configuration. While there are many ways to define the
```solr/home``` (where the index and configuration files are located),
one of the easiest ways to deal with this is by deploying Solr with a
Context which defines the path to where the ```SolrSearch``` plugin 
directory is located (specifically the ```SolrSearch/solr-home```
directory).

The following is an example of a context file that can be deployed
easily through the [Tomcat Manager][tomcatmanager]:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Context docBase="/path/to/solr.war" debug="0" crossContext="true" >
	<Environment name="solr/home" type="java.lang.String" value="/path/to/Omeka/plugins/SolrSearch/solr-home" override="true" />
</Context>
```

It is worth noting that the ```solr-home``` directory can be placed
anywhere on the server that makes sense from a maitenance perspective,
which is valuable for institutions using the [multicore][multicore]
feature in Solr.

Installation and Configuration
----------
* Upload the 'SolrSearch' plugin directory to your Omeka installation's ```plugin``` directory. See [Installing a Plugin][plugininstall].
* Activate the plugin from the admin → Settings → Plugins page.
* Configure your connection to the Solr server. We have provided some
  typical default settings, but double check to ensure that these
settings are correct for you installation of Solr.

## Index Configuration

SolrSearch comes with some pre-selected defaults, including titles,
itemtype, tag, and references to files. These are configurable in the
Solr Index tab of Omeka's Admin interface:

TODO: add image

The index configuration is split in to the different types of
information Omeka uses, plus a few 'special' fields (image, tag,
collection, itemtype) that are outside of the normal classification.
Each element has three options:

* **Is Searchable**: Adds the text of the field to the search index.
* **Is Facet**: Adds the field as a 'facet' in the search interface
* **Is Sortable**: ??? Are we removing this?

After you have configured the fields you want indexed, and how you want
them indexed, click on the ```Save Facets``` button. 

**Note:** SolrSearch indexes any item marked 'public' in Omeka. 

## Hit Highlighting
SolrSearch uses 'hit highlighting' to contextualize the query result.
You can configure this in the ```Hit Highlighting Options``` tab.

## Reindexing


# Developer Mode
There are a number of technologies used in the development mode for this
plugin. You will need [node][node], [ruby gems][gems], as well as
several gems (installed via [bundler][bundler]), and I recommend
[rvm][rvm].

Install the solr keg from [homebrew][homebrew]. You can then start an
instance of Solr with

```bash
solr path/to/SolrSearch/solr-home
```

From scratch, assuming [homebrew][homebrew] is installed.

```bash
#! /bin/bash
brew install node solr
curl http://npmjs.org/install.sh | sh
rvm gemset create solrsearch
```

```bash
brew install solr
cat >> alias solr='solr path/to/SolrSearch/solr-home'
solr
```

## Dependencies

We use an assortment of tools in our development cycle to automate
various tasks. 

* [Rake][rake]: An Make-like program implemented in Ruby. Runs
  [Jasmine][jasmin] BDD JavaScript tests 
* [Cake][cake]: `cake` is a simplified version of Make (Rake, etc.) for
  CoffeeScript. This is used to compile JavaScript files, walk and
mangle the AST, and generate an optimized version of the JavaScript
using [uglify-js][uglify].
* [Ant][ant]: A Java tool and library used to build software. 
* [Guard][guard]: A tool that monitors file-system modification. When a
  file in this project changes, Guard automagically rebuilds compiled
sources and refreshes web pages. 
* [Pear][pear]: A framework and distribution system for reusable PHP components. We use various PHP libraries to run reports, test our code, and generate documentation. 
* [SCSS][sass]: SCSS is a syntax of SASS (syntactically awesome
  stylesheets). We use this to simplify writing stylesheets for our
plugins
* [Compass][compass]: We use this to compile our SCSS, as well as add in
  mixins for CSS3 support. 

### Guard + Compass + LiveReload + uglify + rvm
We use [RVM][rvm] to manage Ruby version, and there is an `.rvmrc` file
to automatically switch bundles and make sure all of the require Ruby
gems are install. If you do not have RVM installed, you can do all of
this manually:

```bash
cd /path/to/SolrSearch
bundle
```

This will install all of the required gems, including the guard gem to
monitor all of the files in the plugin. To start guard, simply run it
within `bundle exec`.

```bash
cd /path/to/SolrSearch
bundle exec guard
```

### Running Tests
SolrSearch tests are designed to run anywhere on the system, as long as 
an environmental variable is set that points to the target Omeka
install. This is especially useful in testing plugins against several
versions of Omeka.

In Bash, you can set this in your .bash_profile/.bash_rc file:

```bash
export OMEKA_DIR=/path/to/omeka
```

With this set, you can run the `phpunit` tests in tests:

```bash
cd /path/to/omeka/tests
phpunit
```

This will generate a test coverage report in the build sub-directory.

### Reports
This plugin uses various tools to analyze software quality from PEAR
including:

* [PHP_Depend][pdepend]: Static code analysis for PHP
* [phpDocumentor][phpdoc]: Generates documentation from PHP source code
* [PHPMessDetector][phpmd]: Code quality analysis
* [phpcpd][phpcpd]: Copy/Paste Detector (CPD) for PHP code
* [PHP_CodeSniffer][phpcs]: Tokenises PHP, JavaScript and CSS files and detects violations of a defined set of coding standards
* [PHPUnit][phpunit]: Automated testing for tests
* [PHP_CodeBrowser][phpcb]: Generates a browsable representation of PHP code where sections with violations found by quality assurance tools

### Packaging

There are two tasks for packaging the plugin. An arbitrary zip (and
tarball) can be generated with the version number and a timestamp with
the `ant zip` task.

```bash
$ ant zip
Buildfile: /path/to/SolrSearch/build.xml

zip:
      [zip] Building zip: /path/to/SolrSearch/build/dist/SolrSearch-20120618-1612.zip

BUILD SUCCESSFUL
Total time: 1 second
```

Likewise, a package suitable for uploading the the Omeka plugin
repository may be generated with the `ant package` task.

```bash
$ ant package
Buildfile: /path/to/SolrSearch/build.xml

clean:
   [delete] Deleting directory /path/to/SolrSearch/build

package:
    [mkdir] Created dir: /path/to/SolrSearch/build/dist
      [zip] Building zip: /path/to/SolrSearch/build/dist/SolrSearch-1.0.zip

BUILD SUCCESSFUL
Total time: 1 second
```

# Reporting Bugs and Contributing
If you discover a problem with SolrSearch, we would like to know about it. However, we ask that you please review these guidelines before submitting a bug report: [Bug reports][bugs].

We hope that you will consider contributing to SolrSearch. Please read this short overview for some information about to to get started: [Contributing][contributing].

## Translations

We'd welcome any help we can get translating text in the SolrSearch plugin.
We're using [Transifex][transifex] to manage translations. For more information
about using this, see [Jeremy's excellent blog post][i18nblog] about it.

[bugs]: https://github.com/scholarslab/SolrSearch/wiki/Bug-Reports
[contributing]: https://github.com/scholarslab/SolrSearch/wiki/Contributing

[phpcb]: https://github.com/Mayflower/PHP_CodeBrowser

[pdepend]: http://pdepend.org/
[phpdoc]: http://www.phpdoc.org/
[phpmd]: http://phpmd.org/
[phpcs]: http://pear.php.net/package/PHP_CodeSniffer/
[phpunit]: http://www.phpunit.de/manual/current/en/index.html
[phpcpd]: https://github.com/sebastianbergmann/phpcpd

[1]: http://scholarslab.org/ "http://scholarslab.org/"
[2]: http://lucene.apache.org/solr/#getstarted "http://lucene.apache.org/solr/#getstarted"
[3]: https://github.com/scholarslab/SolrSearch "https://github.com/scholarslab/SolrSearch"
[4]: http://github.com/scholarslab/SolrSearch/tarball/master "http://github.com/scholarslab/SolrSearch/tarball/master"
[5]: /codex/Installing_a_Plugin "Installing a Plugin"
[homebrew]: http://mxcl.github.com/homebrew/

[node]: http://nodejs.org/
[gems]: http://rubygems.org/
[bundler]: http://gembundler.com/
[rvm]: http://beginrescueend.com/
[solr]: http://lucene.apache.org/solr
[solrinstall]: http://wiki.apache.org/solr/SolrInstall
[tomcatmanager]: http://tomcat.apache.org/tomcat-6.0-doc/manager-howto.html
[multicore]: http://wiki.apache.org/solr/CoreAdmin
[rake]: http://rubygems.org/gems/rake
[cake]: http://coffeescript.org/documentation/docs/cake.html
[jasmin]: http://pivotal.github.com/jasmine/
[uglify]: https://github.com/mishoo/UglifyJS
[ant]: http://ant.apache.org/
[guard]: https://github.com/guard/guard
[pear]: http://pear.php.net/
[sass]: http://sass-lang.com/
[compass]: http://compass-style.org/
[rvm]: https://rvm.io/

[transifex]: http://www.transifex.com/
[i18nblog]: http://www.scholarslab.org/slab-code/translating-neatline/
