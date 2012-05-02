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
