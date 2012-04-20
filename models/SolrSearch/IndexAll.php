<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

require_once SOLR_SEARCH_PLUGIN_DIR . '/lib/SolrSearch/DbPager.php';

class SolrSearch_IndexAll extends ProcessAbstract
{
	public function run($args)
	{
		$solr  = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);
		$db    = get_db();
        $sql   = $db
            ->getTable('Item')
            ->getSelect()
            ->where('public=1')
            ->order('id');
        $pager = new SolrSearch_DbPager($db, $sql);

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
		try {
			$solr->optimize();
		}
		catch ( Exception $e ) {
            $this->_log($e->getMessage());
			echo $e->getMessage();
		}
	}
}
