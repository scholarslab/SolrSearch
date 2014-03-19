About SolrSearch 
----------

**SolrSearch** is an Omeka plugin that allows you to leverage the powerful [Solr][solr] search engine within Omeka. Not only does Solr provide robust, configurable, full-text indexing, it also allows you a flexible interface to configure how your users discover the content in your Omeka application. 

## Requirements

**SolrSearch** relies on access to a [Solr 3.5+][solr] server to maintain the search indexes. Installation of this software is covered in the [Solr Documentation][2]. 

### Configuration
Once Solr is up-and-running, you will need to tell Solr about the the Omeka SolrSearch configuration. While there are many ways to define the `solr/home` (where the index and configuration files are located), one of the easiest ways to deal with this is by deploying Solr with a Context which defines the path to where the `SolrSearch` plugin directory is located (specifically the `SolrSearch/solr-home` directory).

The following is an example of a context file that can be deployed easily through the [Tomcat Manager][tomcatmanager]:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<Context docBase="/path/to/solr.war" debug="0" crossContext="true" >
	<Environment name="solr/home" type="java.lang.String" value="/path/to/Omeka/plugins/SolrSearch/solr-home" override="true" />
</Context>
```

It is worth noting that the `solr-home` directory can be placed anywhere on the server that makes sense from a maitenance perspective, which is valuable for institutions using the [multicore][multicore] feature in Solr.

Installation and Configuration
----------
* Upload the 'SolrSearch' plugin directory to your Omeka installation's `plugin` directory. See [Installing a Plugin][plugininstall].
* Activate the plugin from the admin → Settings → Plugins page.
* Configure your connection to the Solr server. We have provided some typical default settings, but double check to ensure that these settings are correct for you installation of Solr.  

## Index Configuration

SolrSearch comes with some pre-selected defaults, including titles, itemtype, tag, and references to files. These are configurable in the Solr Index tab of Omeka's Admin interface:

The index configuration is split in to the different types of information Omeka uses, plus a few 'special' fields (image, tag, collection, itemtype) that are outside of the normal classification. Each element has three options:

* **Is Indexed**: Adds the text of the field to the search index.
* **Is Facet**: Adds the field as a 'facet' in the search interface

After you have configured the fields you want indexed, and how you want them indexed, click on the `Save Facets` button. 

**Note:** SolrSearch indexes any item marked 'public' in Omeka. 

## Hit Highlighting

SolrSearch uses 'hit highlighting' to contextualize the query result. You can configure this in the `Hit Highlighting Options` tab.

## Reindexing

TODO

# Developer Mode
There are a number of technologies used in the development mode for this plugin. You will need [node][node], [ruby gems][gems], as well as several gems (installed via [bundler][bundler]), and I recommend [rvm][rvm].

Install the solr keg from [homebrew][homebrew]. You can then start an instance of Solr with

```bash
$ solr path/to/SolrSearch/solr-home
```

From scratch, assuming [homebrew][homebrew] is installed.

```bash
#! /bin/bash
$ brew install node solr
$ rvm gemset create solrsearch
$ solr path/to/SolrSearch/solr-core
```

## Tomcat

If you want to run this in the Tomcat servlet container, there is a slightly different method of configuring the application.

Install start Tomcat:

```bash
$ brew install tomcat
```

We're going to need to refer to a directory inside where brew installed tomcat, and we'll need to pass it to some of the scripts that we run later. Let's find out what it is and save it.

```bash
$ CATALINA_HOME=$(ls -d /usr/local/Cellar/tomcat/* | head -1)/libexec
$ export CATALINA_HOME
$ echo $CATALINA_HOME
/usr/local/Cellar/tomcat/7.0.50/libexec
```

The output of the `echo` command on the last line may have a different version number, but everything else should look the same.


Now enable the manager application. To do this, edit `/usr/local/Cellar/tomcat/[version]/libexec/conf/tomcat-users.xml`. The first part of the path should be the same as the output of the `echo` command above. Inside the `<tomcat-users>` element, add something along the following:

```xml
<role rolename="manager-gui"/>
<user username="tomcat" password="s3cret" roles="manager-gui"/>
```

You need to have [Solr](http://lucene.apache.org/solr/) downloaded somewhere on your computer.

You need to copy some files that are shipped with Solr to get included in your Tomcat `$PATH`. These are the files in the `examples/lib/ext/` directory. You will have something that looks like this: 

```bash
cp path/to/solr/download/examples/lib/ext/*.jar $CATALINA_HOME/lib/
```

Now start Tomcat with the catalina shell:

```bash
$ catalina start
```
This will run Tomcat as a background process on port `8080`, which you can access at `http://localhost:8080`. 

For Tomcat, it's easiest to pass the various values that Solr needs in an XML configuration file. Name it `tomcat-config.xml` in your project directory, and have it contain something like this: 

```xml
<Context path="/solr" docBase="/Users/[username]/Downloads/solr-4.6.0/dist/solr-4.6.0.war" debug="0" crossContext="true">
  <Environment name="solr/home" type="java.lang.String" value="/Users/[username]/projects/SolrSearch/solr-core" override="true"/>
</Context>
```

**Note:** Adjust your paths as necessary.

Now to access the manager application. Point your browser at `http://localhost:8080/manager/html`. In the deploy section fill out the **Context** and **XML Configuration file URL** fields with: 

* **Context** /solr
* **XML Configuration file URL** path/to/tomcat-config.xml

Then hit the deploy button. If everything went well, you should see the Solr admin panel when you point your browser at `http://localhost:8080/solr`

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
