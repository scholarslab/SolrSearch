<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_Job_IndexAll extends Omeka_Job_Process_AbstractProcess
{


    /**
     * (Re)index all Solr records.
     */
    public function run($args)
    {
        try {
            SolrSearch_Helpers_Index::indexAll(array());
        } catch ( Exception $e ) {
            $this->_log($e->getMessage());
            echo $e->getMessage();
        }
    }


}
