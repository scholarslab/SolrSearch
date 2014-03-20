# [SolrSearch][plugin]

**SolrSearch** replaces the default Omeka search interface with one powered by [Solr][solr], a scalable and feature-rich searching platform for full-text searching. In most cases, Omeka's built-in searching capabilities work great, but there are a couple situations where Solr might make sense:

  - When you have a _really_ large collection (many tens of thousands of items), and want something a bit faster;

  - When your site contains a lot of text content, and you want to take advantage of Solr's "hit highlighting" functionality, which makes it possible to display a snippet from each of the matching records in the list of results with the search terms displayed in bold;

  - When your site makes use of lots of different _taxonomies_ (collections, item types, etc.), and you want to make use of Solr's "faceting" capabilities, which make it possible for users to progressively refine searches by cropping down the set of results by tag, collection, type, etc.

## Requirements

To use the plugin, you'll need access to an installation of Solr 4.0+ running the "core" included in the plugin source code under `solr-core/omeka`. Head over to the [Solr installation documentation][solr-install] for general information, and see below for instructions for how to get up and running with a local testing installation.

## Installation

Once Solr is up and running, install SolrSearch just like any other Omeka plugin:

  1. Download the .zip directory from the [Omeka addons repository][plugin] and unzip the archive.

  2. Upload the `SolrSearch` directory into the `plugins` directory in you Omeka installation.

  3. Open up the "Plugin" page in the Omeka admin, and click the green "Install" button for Solr Search.

For more information about installing Omeka plugins, check out the [Managing Plugins][managing-plugins] guide in the Omeka Codex.

## Usage

Once all the pieces are in place, the first step is to point the SolrSearch plugin at your Solr instance. Once the link is established, other configuration options make it possible to customize how the content is indexed and how the search results are displayed in the public interface.

Open up the configuration options by clicking on on the "Solr Search" tab in the Omeka admin.

### Configuration

#### Server Configuration

Use these fields to set the Solr connection parameters and high-level options:

  - **Server Host**: The location of the Solr server, without the port number.

  - **Server Port**: The port that Solr is listening on.

  - **Core URL**: The URL of the Solr core in which documents should be indexed.

  - **Facet Ordering**: The criteria by which to sort the facets in the results.

  - **Facet Count**: The maximum number of facets to display.

After making changes to the connection parameters, click the "Save Settings" button. If the plugin is able to connect to Solr, a greet notification saying "Solr connection is valid" will be displayed.

#### Field Configuration

This form makes it possible to configure (a) which metadata elements and Omeka categories ("fields") are stored as searchable content in Solr and (b) which fields should be used as "facets", groupings of records that can be used to iteratively narrow down the set of results. For each element, there are three options:

  - **Facet Label**: The label used as the heading for the facet corresponding to the field. In most cases, it probably just makes sense to use the canonical name as the element (the default), but this makes it possible to create a customized interface that doesn't map onto the nomenclature of the metadata.

  - **Is Indexed?**: If checked, the content in this field will be stored as full-text-searchable content in Solr. As a rule of thumb, it makes sense to index any fields that contain non-trivial text content, but not fields that contain non-semantic data or identifiers.

  - **Is Facet?**: If checked, the field will be used as a facet in the results. As a rule of thumb, **a field might be a useful facet if it contains a controlled vocabulary**. For example, imagine you use one of three values in the Dublin Core "Type" field - `type1`, `type2`, and `type3`. This would make a good facet, because users would be able to hone in on the implicit relationships among items of the same type. It wouldn't make sense to use something like the "Description" field as a facet, though, two items will almost never share the exact same description (or, at least, they probably shouldn't!).

Use the accordion to expand and contract the fields in the three categories. There are really two types of fields - the "Omeka Categories," which aren't actually metadata elements but rather high-level taxonomies that are baked in to the struture of Omeka, and the metadata elements (Dublin Core and Item Type Metadata) that can be used to describe items.

After you've made changes, click the "Update Search Fields" to save the configuration.

#### Hit Highlighting

Hit highlighting is the feature that makes it possible to display snippets of text for each result in the search interface that excerpt portions of the metadata that are relevant to the query.

  - **Enable Highlighting**: Set whether highlighting snippets should be displayed.

  - **Number of Snippets**: The maximum number of snippets that should be displayed for a given result.

  - **Snippet Length**: The length of each snippet - how much "padding" should be displayed.

Click "Save Settings" to update the configuration.

#### Index Items

After making changes in the "Field Configuration" and "Hit Highlighting" tabs, it's necessary to reindex the content in the site in order for the changes to take effect. SolrSearch doesn't do this automatically because reindexing can take as long as a few minutes for really large sites.

When you're ready, just click the "Clear and Reindex" button. This will spawn off a background process behind the scenes that rebuilds the index according to the new configuration options.

### Searching

Once the content has been (re)indexed, head to the public site and type a seaarch query into the regular Omeka search input. When the query is submitted, SolrSearch will intercept the request and redirect to a custom interface that displays results from Solr with faceting and hit highlighting.


### Configuration

## Server Configuration

TODO

## Field Configuration

SolrSearch comes with some pre-selected defaults, including titles, itemtype, tag, and references to files. These are configurable in the Solr Index tab of Omeka's Admin interface:

The index configuration is split in to the different types of information Omeka uses, plus a few 'special' fields (image, tag, collection, itemtype) that are outside of the normal classification. Each element has three options:

* **Is Indexed**: Adds the text of the field to the search index.
* **Is Facet**: Adds the field as a 'facet' in the search interface

After you have configured the fields you want indexed, and how you want them indexed, click on the `Save Facets` button. 

**Note:** SolrSearch indexes any item marked 'public' in Omeka. 

## Hit Highlighting

SolrSearch uses 'hit highlighting' to contextualize the query result. You can configure this in the `Hit Highlighting Options` tab.

## Index Items

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

[plugin]: http://omeka.org/add-ons/plugins/SolrSearch/
[solr]: http://lucene.apache.org/solr
[solr-install]: https://wiki.apache.org/solr/SolrInstall 
[managing-plugins]: https://omeka.org/codex/Managing_Plugins
[homebrew]: http://mxcl.github.com/homebrew/
[node]: http://nodejs.org/
[gems]: http://rubygems.org/
[bundler]: http://gembundler.com/
[rvm]: http://beginrescueend.com/
[multicore]: http://wiki.apache.org/solr/CoreAdmin
[rvm]: https://rvm.io/
