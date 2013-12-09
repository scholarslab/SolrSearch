<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_Facet extends Omeka_Record_AbstractRecord {


    /**
     * Gets an array of all of the CsvImport_Import objects from the database
     * 
     * @return array
     */
    public static function getFacets()
    {
        $db = get_db();
        $agents = $db->getTable('SolrSearch_Facet')->findAll;
        return $agents;
    }
}
