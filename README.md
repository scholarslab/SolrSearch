# [SolrSearch][plugin]

![Solr](https://lucene.apache.org/images/solr.png)

**SolrSearch** replaces the default Omeka search interface with one powered by
[Solr][solr], a scalable and feature-rich search engine that supports faceting
and hit highlighting. In most cases, Omeka's built-in searching capabilities
work great, but there are a couple of situations where it might make sense to
take a look at Solr:

  - When you have a really large collection, and want something a bit faster;

  - When your site contains a lot of text content, and you want to take
    advantage of Solr's hit highlighting functionality, which displays a
    preview snippet from each of the matching records;

  - When your site makes use of a lot of different taxonomies (collections,
    item types, etc.), and you want to use Solr's faceting capabilities, which
    make it possible for users to refine searches by cropping down the set of
    results to focus on specific categories.

## Requirements

To use the plugin, you'll need access to an installation of Solr 4.0+ running
the core included in the plugin source code under `solr-core/omeka`. For
general information about how to get up and running with Solr, check out the
official [installation documentation][solr-install].

## Installation

### Solr Core

To deploy the Solr core, just copy the `solr-core/omeka` directory into your
Solr home directory. For example, if your deployment is based on the default
Solr 4 multicore template, you might end up with directories for `core0`,
`core1`, and `omeka`. Once the directory is in place, restart/reload Solr to
register the new core.

### Omeka Plugin

Once the core is up and running, install SolrSearch just like any other Omeka
plugin:

  1. Download the plugin from the [Omeka addons repository][plugin] and unzip
     the archive.

  2. Upload the `SolrSearch` directory into the Omeka `plugins` directory.

  3. Open up the "Plugins" page in Omeka and click the "Install" button for
     Solr Search.

For more information, check out the [Managing Plugins][managing-plugins] guide.

## Configuration

### Server Configuration

To get started, click on the "Solr Search" tab, which displays a form with Solr
connection parameters:

  - **Server Host**: The location of the Solr server, without the port number.

  - **Server Port**: The port that Solr is listening on.

  - **Core URL**: The URL of the Solr core in which documents should be
    indexed.

After making changes to the connection parameters, click the "Save Settings"
button. If the plugin is able to connect to Solr, a greet notification saying
"Solr connection is valid" will be displayed.

### Collections Configuration

You can also decide not to index certain collections of items. By default, all
collections are indexed. However, if you go to the "Collections" tab, then you
can select collections to *exclude* from indexing.

### Field Configuration

This form makes it possible to configure (a) which metadata elements and Omeka
categories ("fields") are stored as searchable content in Solr and (b) which
fields should be used as "facets", groupings of records that can be used to
iteratively narrow down the set of results.

> If you've installed any new metadata elements and they're missing from this
> form, click the "Load New Elements" button at the bottom of the page. The
> page will reload, and hopefully the new elements will be listed.

For each element, there are three options:

  - **Facet Label**: The label used as the heading for the facet corresponding
    to the field. In most cases, it probably just makes sense to use the
    canonical name as the element (the default), but this makes it possible to
    create a customized interface that doesn't map onto the nomenclature of the
    metadata.

  - **Is Indexed?**: If checked, the content in this field will be stored as
    full-text-searchable content in Solr. As a rule of thumb, it makes sense to
    index any fields that contain non-trivial text content, but not fields that
    contain non-semantic data or identifiers.

  - **Is Facet?**: If checked, the field will be used as a facet in the
    results. As a rule of thumb, **a field might be a useful facet if it
    contains a controlled vocabulary**. For example, imagine you use one of
    three values in the Dublin Core "Type" field - `type1`, `type2`, and
    `type3`. This would make a good facet, because users would be able to hone
    in on the implicit relationships among items of the same type. It wouldn't
    make sense to use something like the "Description" field as a facet,
    though, two items will almost never share the exact same description (or,
    at least, they probably shouldn't!).

Use the accordion to expand and contract the fields in the three categories.
There are two types of fields - the "Omeka Categories," which aren't actually
metadata elements but rather high-level taxonomies that are baked in to the
struture of Omeka, and the metadata elements (Dublin Core and Item Type
Metadata) that can be used to describe items.

After you've made changes, click the "Update Search Fields" to save the
configuration.

### Results Configuration

This form exposes options for two features in Solr: **hit highlighting**, which
makes it possible to display preview snippets for each result that excerpt
portions of the metadata that are relevant to the query, and **faceting**,
which makes it possible for users to progressively refine large result sets by
honing in on specific categories.

  - **Enable Highlighting**: Set whether highlighting snippets should be
    displayed.

  - **Extent of Document Highlightable**: Set the amount of the document to
    scan when highlighting. By default, to save time, this is limited to 51200
    characters. If you have documents in the results that don't have snippets,
    you can make this larger.

  - **Number of Snippets**: The maximum number of snippets to display for a
    result.

  - **Snippet Length**: The maximum length of each snippet.

  - **Facet Ordering**: The criteria by which to sort the facets in the
    results.

  - **Facet Count**: The maximum number of facets to display.

Click "Save Settings" to update the configuration.

### Index Items

After making changes in the "Field Configuration" and "Results Configuration"
tabs, it's necessary to reindex the content in the site in order for the
changes to take effect. SolrSearch doesn't do this automatically because
reindexing can take as long as a few minutes for really large sites.

When you're ready, just click the "Clear and Reindex" button. This will spawn
off a background process behind the scenes that rebuilds the index according to
the new configuration options.

### Private Items

Currently, because of the complexity of the Omeka authorization system, we only
support search on items that have been marked *public*. The admin search
interface can be used to discover items that are private.

### Featured Items

As of version [2.1.0][210], Solr Search indexes and allows faceted searches on
featured items. If you're upgrading, for this to work, you'll need to do two
extra steps after going through the standard Omeka plugin upgrade process.

1. Re-install the Solr configuration files as explained in the section on
[Installing the Solr Core][solr-core].
2. Re-index everything from the SolrSearch admin panel.

## Searching

Once the content has been indexed, head to the public site and type a search query into the regular Omeka search input. When the query is submitted, SolrSearch will intercept the request and redirect to a custom interface that displays results from Solr with faceting and hit highlighting.

## Thanks

This work has been a collaboration. During development, we've gotten help from a number of others:

* @anuragji
* @marcobat
* Adam Doan
* @cokernel
* Dave Lester

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
[210]: https://github.com/scholarslab/SolrSearch/releases/tag/2.1.0
[solr-core]: #solr-core
