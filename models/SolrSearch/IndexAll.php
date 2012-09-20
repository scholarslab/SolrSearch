<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

require_once SOLR_SEARCH_PLUGIN_DIR . '/lib/SolrSearch/DbPager.php';

class SolrSearch_IndexAll extends ProcessAbstract
{
    public function run($args)
    {
        $solr   = new Apache_Solr_Service(
            get_option('solr_search_server'),
            get_option('solr_search_port'),
            get_option('solr_search_core')
        );

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
                $doc = SolrSearch_IndexHelpers::itemToDocument($db, $item);
                $docs[] = $doc;
                $solr->addDocuments($docs);
            }
            try {
                $solr->commit();
            } catch (Exception $e) {
                echo $e->getMessage();
                throw $e;
            }
        }

        // Now the other addon stuff.
        try {
            $mgr  = new SolrSearch_Addon_Manager($db);
            $docs = $mgr->reindexAddons();
            $solr->addDocuments($docs);
            $solr->commit();
        } catch (Exception $e) {
            echo $e->getMessage();
            throw $e;
        }

        try {
            $solr->optimize();
        }
        catch ( Exception $e ) {
            $this->_log($e->getMessage());
            echo $e->getMessage();
        }
    }
}
