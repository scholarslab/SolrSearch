<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

class SolrSearch_DeleteAll extends ProcessAbstract
{
    public function run($args)
    {
        try {
            $solr = new Apache_Solr_Service(
                get_option('solr_search_server'),
                get_option('solr_search_port'),
                get_option('solr_search_core')
            );

            $solr->deleteByQuery('*:*');
            $solr->commit();
            $solr->optimize();
        } catch ( Exception $e ) {
            $this->_log($e->getMessage());
            echo $e->getMessage();
        }
    }
}
