<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 *
 * PHP version 5
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package     omeka
 * @subpackage  fedoraconnector
 * @author      Scholars' Lab <>
 * @author      Ethan Gruber <ewg4x@virginia.edu>
 * @author      Adam Soroka <ajs6f@virginia.edu>
 * @author      Wayne Graham <wayne.graham@virginia.edu>
 * @author      Eric Rochester <err8n@virginia.edu>
 * @copyright   2010 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 * @version     $Id$
 * @link        http://omeka.org/add-ons/plugins/SolrSearch/
 */

/**
 * This contains some helpers for indexing items.
 **/
class SolrSearch_IndexHelpers
{
    /**
     * This takes an Omeka_Record instance and returns a populated 
     * Apache_Solr_Document.
     *
     * @param Omeka_Record $item The record to index.
     *
     * @return Apache_Solr_Document
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function itemToDocument($db, $item)
    {
        $doc = new Apache_Solr_Document();
        $doc->id = $item['id'];

        $elementTexts = $db
            ->getTable('ElementText')
            ->findBySql('record_id = ?', array($item['id']));
        foreach ($elementTexts as $elementText) {
            $fieldName = $elementText['element_id'] . '_s';
            $doc->setMultiValue($fieldName, $elementText['text']);

            if ($elementText['element_id'] == 50) {
                $doc->setMultiValue('title', $elementText['text']);
            }
        }

        foreach ((array)$item->Tags as $key => $tag) {
            $doc->setMultiValue('tag', $tag);
        }

        if ($item['collection_id'] > 0) {
            $collectionName = $db
                ->getTable('Collection')
                ->find($item['collection_id'])
                ->name;
            $doc->collection = $collectionName;
        }

        // Item Type
        if ($item['item_type_id'] > 0) {
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

        // FedoraConnector XML
        if (function_exists('fedora_connector_installed')) {
            $dataStreams = $db
                ->getTable('FedoraConnector_Datastream')
                ->findBySql('mime_type=? AND item_id=?', array('text/xml', $item->id));

            foreach ($dataStreams as $ds) {
                $fedoraFile = fedora_connector_content_url($ds);
                $this->_indexXml($fedoraFile, $doc);
            }
        }

        return $doc;
    }

    /**
     * This index the content of an XML file into a Solr document.
     *
     * @param string               $filename The name of the file to index.
     * @param Apache_Solr_Document $solrDoc  The document to index into.
     *
     * @return null
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    protected static function _indexXml($filename, $solrDoc) {
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
     **/
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
