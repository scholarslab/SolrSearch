About SolrSearch 
----------

SolrSearch is a plugin developed by the [Scholars' Lab][1] at the University of Virginia Library.  It is designed to replace the default Omeka database search mechanism with Solr, a powerful Java-based search index and query system.  It offers more robust full-text searching, faceted browsing, sorting, and hit highlighting than the MySQL search.  The plugin can be used as a foundation for future controlled vocabulary plugins.

This plugin is a beta release

Prerequisites 
=============

SolrSearch requires an active Solr index.  Solr is a Java application that is generally run in a container like Jetty or Tomcat.  Refer to [documentation][2] for instructions on downloading and installing it.  In the SolrSearch plugin directory, there is a folder called solr-home.  This folder contains configuration files for the index.  It can be copied and pasted elsewhere on the server or Tomcat can be configured to refer to this folder as the solr/home context path with the following XML snipped called solr.xml and placed in /path/to/tomcat/conf/Catalina/localhost/

<?xml version="1.0" encoding="UTF-8"?>
<Context docBase="/path/to/solr.war" debug="0" crossContext="true" >
	<Environment name="solr/home" type="java.lang.String" value="/path/to/Omeka/plugins/SolrSearch/solr-home" override="true" />
</Context>

After successfully starting an instance of Solr on a server that can be accessed from your Omeka installation, SolrSearch is ready to be installed and configured.

Download 
----------
*  Git: [https://github.com/scholarslab/SolrSearch][3]
*  Package: [SolrSearch 1.1][4]

Features 
----------

*  Easily configurable Solr server settings

*  Easily configurable checkbox form for selecting display, sort, and facet fields

*  Hit highlighting

*  Robust full-text search with relevancy ranking that can accommodate Lucene query syntax

*  Faceted browse based on selected elements, tags, or collection name

*  Sorting of documents based on select elements, tags, item type, or collection name

*  Indexable and displayable image files per item

*  Well-designed and intuitive public interface for search results

Installing and Configuring 
----------

1.  PHP-CLI is required for indexing documents in the background.  This can be installed through package managers on most Linux systems.  Refer to Google for instructions for installing the packages on your operating system.

2.  Checkout from svn or download/extract zipped package to omeka/plugins (see [Installing_a_Plugin][5]).

3.  Set appropriate write permissions for your solr-home/data folder.

4.  Install SolrSearch on the Settings->Plugins page.

5.  Configure the plugin with server information and results per page, facet limit, and facet sort order parameters.  The default server configuration will work with any single core  Solr index running in Tomcat (port 8080) that is installed on the same server as your Omeka instance.

6.  Use the Configure Solr tab to select displayable, facet, and sortable fields that will go into effect in the public interface.  From this section, the user can click on a tab to view Solr highlighting options and also reindex all Omeka items, which may be necessary to do from time to time if multiple plugins that use the after_save_item hook are installed.

7.  Now you need to make a minor modification to your theme to replace the simple_search database query with the Solr search form.

* Edit the header.php for your theme located in omeka/themes/[theme name]/common/header.php (or wherever simple_search() is called).

* Replace simple_search() with the solr_search_form() function.

* You may wish to remove or comment out the Advanced Search function: link_to_advanced_search().  SolrSearch is capable of a number of advanced features that reduce the usefulness of the default advanced search.

* You may also wish to change the Browse Items link to the URL of the search results when querying all records:

'Browse Items' => uri('solr-search/results/?q=*:*')

After these changes have been made, you will be able to use the search box in the header to query Solr and filter results by facet.

Indexing to Solr 
----------

Upon plugin installation, all items designated as 'public' will be indexed into Solr.  Items that are not public are ignored by the indexing script.  When an item is saved and the item is public, the updated metadata is posted to Solr.  If that item was previously public and that designation was removed, the associated document will be removed from Solr.  Items not designated as public already are ignored.  When an item is deleted from Omeka, its corresponding Solr document is also deleted.  When SolrSearch is uninstalled, all Solr documents are purged.

<!-- 
NewPP limit report
Preprocessor node count: 13/1000000
Post-expand include size: 0/2097152 bytes
Template argument size: 0/2097152 bytes
Expensive parser function count: 0/100
-->

Retrieved from "[http://omeka.org/codex/Plugins/SolrSearch](http://omeka.org/codex/Plugins/SolrSearch)"

[1]: http://scholarslab.org/ "http://scholarslab.org/"
[2]: http://lucene.apache.org/solr/#getstarted "http://lucene.apache.org/solr/#getstarted"
[3]: https://github.com/scholarslab/SolrSearch "https://github.com/scholarslab/SolrSearch"
[4]: http://github.com/scholarslab/SolrSearch/tarball/master "http://github.com/scholarslab/SolrSearch/tarball/master"
[5]: /codex/Installing_a_Plugin "Installing a Plugin"
