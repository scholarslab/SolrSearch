<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */


/**
 * CsvImport_Import - represents a csv import event
 * 
 * @version $Id$ 
 * @package CsvImport
 * @author CHNM
 * @copyright Center for History and New Media, 2008
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 **/
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
