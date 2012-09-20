<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

class SolrSearch_Facet extends Omeka_Record {


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
