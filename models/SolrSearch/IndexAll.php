<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

require_once SOLR_SEARCH_PLUGIN_DIR . '/lib/SolrSearch/DbPager.php';

class SolrSearch_IndexAll extends Omeka_Job_Process_AbstractProcess
{
    public function run($args)
    {
        try {
            SolrSearch_IndexHelpers::indexAll(array());
        } catch ( Exception $e ) {
            $this->_log($e->getMessage());
            echo $e->getMessage();
        }
    }
}
