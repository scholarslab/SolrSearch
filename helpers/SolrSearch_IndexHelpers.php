<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

require_once HELPER_DIR . '/UrlFunctions.php';

/**
 * This contains some helpers for indexing items.
 */
class SolrSearch_IndexHelpers
{
    /**
     * This takes an Omeka_Record instance and returns a populated 
     * Apache_Solr_Document.
     *
     * @param Omeka_Db     $db   The database to query.
     * @param Omeka_Record $item The record to index.
     *
     * @return Apache_Solr_Document
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function itemToDocument($db, $item)
    {
        $doc = new Apache_Solr_Document();
        $doc->id = "Item_{$item['id']}";
        $doc->setField('model', 'Item');
        $doc->setField('modelid', $item['id']);
        $doc->setField('url', SolrSearch_IndexHelpers::getUri($item));

        $indexSet = SolrSearch_IndexHelpers::getIndexSet($db);

        $elementTexts = $db
            ->getTable('ElementText')
            ->findBySql('record_id = ?', array($item['id']));

        foreach ($elementTexts as $elementText) {
            if (array_key_exists($elementText['element_id'], $indexSet)) {
                $fieldName = $indexSet[$elementText['element_id']];
                $doc->setMultiValue($fieldName, $elementText['text']);

                if ($elementText['element_id'] == 50) {
                    $doc->setMultiValue('title', $elementText['text']);
                }
            }
        }

        $doc->setMultiValue('resulttype', 'Item');

        if (array_key_exists('tag', $indexSet)) {
            foreach ($item->getTags() as $tag) {
                $doc->setMultiValue('tag', $tag->name);
            }
        }

        if (array_key_exists('collection', $indexSet)
            && $item['collection_id'] > 0
        ) {
            $collectionName = $db
                ->getTable('Collection')
                ->find($item['collection_id'])
                ->name;
            $doc->collection = $collectionName;
        }

        // Item Type
        if (array_key_exists('itemtype', $indexSet) && $item['item_type_id'] > 0) {
            $itemType = $db
                ->getTable('ItemType')
                ->find($item['item_type_id'])
                ->name;
            $doc->itemtype = $itemType;
        }

        // Images
        $files = $item->Files;
        foreach ((array)$files as $file) {
            $mimeType = $file->mime_browser;
            if ($file->has_derivative_image == 1) {
                $doc->setMultiValue('image', $file['id']);
            }
        }

        return $doc;
    }

    /**
     * This returns the URI for an Omeka_Record.
     *
     * @param Omeka_Record $record The record to return the URI for.
     *
     * @return string $uri The URI to access the record with.
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function getUri($record)
    {
        $uri    = '';
        $action = 'show';
        $rc     = get_class($record);

        if ($rc === 'SimplePagesPage') {
            if (simple_pages_is_home_page($record)) {
                $uri = abs_uri('');
            } else {
                $uri = uri($record->slug);
            }

        } else if ($rc === 'ExhibitSection') {
            $exhibit = $record->getExhibit();
            $exUri   = SolrSearch_IndexHelpers::getSlugUri($exhibit, $action);
            $uri     = "$exUri/{$record->slug}";

        } else if ($rc === 'ExhibitPage') {
            $section = $record->getSection();
            $exhibit = $section->getExhibit();
            $exUri   = SolrSearch_IndexHelpers::getSlugUri($exhibit, $action);
            $uri     = "$exUri/{$section->slug}/{$record->slug}";

        } else if (property_exists($record, 'slug')) {
            $uri = SolrSearch_IndexHelpers::getSlugUri($record, $action);

        } else {
            $uri = record_uri($record, $action);
        }

        // These should never be under /admin/, so remove that if it's there.
        $uri = preg_replace('|^/admin/|', '/', $uri, 1);

        return $uri;
    }

    /**
     * This returns the URL for an Omeka_Record with a 'slug' property.
     *
     * @param Omeka_Record $record The sluggable record to create the URL for.
     * @param string       $action The action to access the record with.
     *
     * @return string $uri The URI for the record.
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function getSlugURI($record, $action)
    {
        // Copied from omeka/applications/helpers/UrlFunctions.php, record_uri.
        // Yuck.
        $recordClass = get_class($record);
        $inflector   = new Zend_Filter_Word_CamelCaseToDash();
        $controller  = strtolower($inflector->filter($recordClass));
        $controller  = Inflector::pluralize($controller);
        $options     = array(
            'controller' => $controller,
            'action'     => $action,
            'id'         => $record->slug
        );
        $uri = uri($options, 'id');

        return $uri;
    }

    /**
     * This returns a set of fields to be indexed by Solr according to the
     * solr_search_facet table.
     *
     * The fields can be either the element IDs or the names of categories like
     * 'description'.
     *
     * @param Omeka_Db $db The database to query.
     *
     * @return array $fieldSet The set of fields to index.
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function getIndexSet($db)
    {
        $fieldSet = array();

        $facets = $db
            ->getTable('SolrSearchFacet')
            ->findAll();

        foreach ($facets as $facet) {
            if ($facet->is_displayed || $facet->is_facet) {
                $key = $facet->element_id ? $facet->element_id : $facet->name;
                $fieldSet[$key] = $facet->name;
            }
        }

        return $fieldSet;
    }

    /**
     * This index the content of an XML file into a Solr document.
     *
     * @param string               $filename The name of the file to index.
     * @param Apache_Solr_Document $solrDoc  The document to index into.
     *
     * @return null
     * @author Eric Rochester <erochest@virginia.edu>
     */
    protected static function _indexXml($filename, $solrDoc)
    {
        $xml = new DomDocument();
        $xml->load($filename);
        $xpath = new DOMXPath($xml);

        $nodes = $xpath->query('//text()');
        foreach ($nodes as $node) {
            $value = preg_replace('/\s\s+/', ' ', trim($node->nodeValue));
            if ($value != ' ' && $value != '') {
                $solrDoc->setMultiValue('fulltext', $value);
            }
        }
    }

    /**
     * This pings the Solr server with the given options and returns true if 
     * it's currently up.
     *
     * @param array $options The configuration to test. Missing values will be 
     * pulled from the current set of options.
     *
     * @return bool
     * @author Eric Rochester <erochest@virginia.edu>
     */
    public static function pingSolrServer($options)
    {
        $server = $options['solr_search_server'] or get_option('solr_search_server');
        $port   = $options['solr_search_port']   or get_option('solr_search_port');
        $core   = $options['solr_search_core']   or get_option('solr_search_core');
        $solr   = new Apache_Solr_Service($server, $port, $core);
        return $solr->ping();
    }

}


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
