<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_Helpers_Index
{


    /**
     * Connect to Solr.
     *
     * @param array $options An array of connection parameters.
     *
     * @return Apache_Solr_Service
     * @author David McClure <david.mcclure@virginia.edu>
     **/
    public static function connect($options=array())
    {

        $server = array_key_exists('solr_search_host', $options)
            ? $options['solr_search_host']
            : get_option('solr_search_host');

        $port = array_key_exists('solr_search_port', $options)
            ? $options['solr_search_port']
            : get_option('solr_search_port');

        $core = array_key_exists('solr_search_core', $options)
            ? $options['solr_search_core']
            : get_option('solr_search_core');

        return new Apache_Solr_Service($server, $port, $core);

    }


    /**
     * This takes an Omeka_Record instance and returns a populated 
     * Apache_Solr_Document.
     *
     * @param Omeka_Record $item The record to index.
     *
     * @return Apache_Solr_Document
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function itemToDocument($item)
    {

        $db = get_db();

        // Create the item document.
        $doc = new Apache_Solr_Document();
        $doc->id = "Item_{$item->id}";
        $doc->setMultiValue('resulttype', 'Item');
        $doc->setField('model', 'Item');
        $doc->setField('modelid', $item->id);

        // Gather all element texts.
        $texts = $db->getTable('ElementText')->findByRecord($item);

        // Get indexed elements.
        $indexed = self::getIndexSet();

        // Index element texts:
        foreach ($texts as $text) {

            // If the element text should be searchable.
            if (array_key_exists($text->element_id, $indexed)) {

                // Get the Solr key for the element.
                $slug = $indexed[$text->element_id];

                // Set string and text fields on the document.
                $doc->setMultiValue("{$slug}_s", $text->text);
                $doc->setMultiValue("{$slug}_t", $text->text);

                // If the title is searchable, set it explicitly.
                if ($text->element_id == 50) {
                    $doc->setMultiValue('title', $text->text);
                }

            }

        }

        // Index tags:
        if (array_key_exists('tag', $indexed)) {
            foreach ($item->getTags() as $tag) {
                $doc->setMultiValue('tag', $tag->name);
            }
        }

        // Index collection title:
        if (array_key_exists('collection', $indexed) &&
          $item->collection_id > 0) {

            $collection = $item->getCollection();
            $doc->collection = metadata(
                $collection, array('Dublin Core', 'Title')
            );

        }

        // Index item type:
        if (array_key_exists('itemtype', $indexed) &&
            $item->item_type_id > 0) {

            $itemType = $item->getItemType();
            $doc->itemtype = $itemType->name;

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
            $uri = url($record->slug);
        }

        else if ($rc === 'ExhibitPage') {

            $exhibit = $record->getExhibit();
            $exUri   = self::getSlugUri($exhibit, $action);
            $uri     = "$exUri/$record->slug";

        } else if (property_exists($record, 'slug')) {
            $uri = self::getSlugUri($record, $action);
        } else {
            $uri = record_url($record, $action);
        }

        // Always index public URLs.
        $uri = preg_replace('|/admin/|', '/', $uri, 1);

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
        $uri = url($options, 'id');

        return $uri;
    }


    /**
     * This returns a set of fields to be indexed by Solr according to the
     * solr_search_facet table. The fields can be either the element IDs or
     * the names of categories like 'description'.
     *
     * @return array $fieldSet The set of fields to index.
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function getIndexSet()
    {
        $fieldSet = array();

        $facets = get_db()->getTable('SolrSearchField')->findAll();

        foreach ($facets as $facet) {
            if ($facet->is_indexed || $facet->is_facet) {
                $key = $facet->element_id ? $facet->element_id : $facet->slug;
                $fieldSet[$key] = $facet->slug;
            }
        }

        return $fieldSet;
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
    public static function pingSolrServer($options=array())
    {
        try {
            return self::connect($options)->ping();
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * This re-indexes everything in the Omeka DB.
     *
     * @return void
     * @author Eric Rochester
     **/
    public static function indexAll($options=array())
    {

        $solr = self::connect($options);

        $db     = get_db();
        $table  = $db->getTable('Item');
        $select = $table->getSelect();

        $table->filterByPublic($select, true);
        $table->applySorting($select, 'id', 'ASC');

        // First get the items.
        $pager = new SolrSearch_DbPager($db, $table, $select);
        while ($items = $pager->next()) {
            foreach ($items as $item) {
                $docs = array();
                $doc = self::itemToDocument($item);
                $docs[] = $doc;
                $solr->addDocuments($docs);
            }
            $solr->commit();
        }

        // Now the other addon stuff.
        $mgr  = new SolrSearch_Addon_Manager($db);
        $docs = $mgr->reindexAddons();
        $solr->addDocuments($docs);
        $solr->commit();

        $solr->optimize();

    }


    /**
     * This deletes everything in the Solr index.
     *
     * @param array $options The configuration to test. Missing values will be
     * pulled from the current set of options.
     *
     * @return void
     * @author Eric Rochester
     **/
    public static function deleteAll($options=array())
    {

        $solr = self::connect($options);

        $solr->deleteByQuery('*:*');
        $solr->commit();
        $solr->optimize();

    }


}
