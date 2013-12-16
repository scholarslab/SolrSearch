<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_DeleteAll extends Omeka_Job_Process_AbstractProcess
{


    /**
     * Delete all Solr records.
     */
    public function run()
    {
        try {
            SolrSearch_IndexAll::deleteAll(array());
        } catch ( Exception $e ) {
            $this->_log($e->getMessage());
            echo $e->getMessage();
        }
    }


}
