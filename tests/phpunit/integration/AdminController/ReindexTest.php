<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class AdminControllerTest_Reindex extends SolrSearch_Case_Default
{


    /**
     * REINDEX should clear the Solr index and reindex all records.
     */
    public function testReindex()
    {

        // Add items.
        $item1 = insert_item(array('public' => true));
        $item2 = insert_item(array('public' => true));

        // Delete the Solr records.
        SolrSearch_Helpers_Index::deleteAll();

        // Index should be empty.
        $this->assertEquals(0, $this->_countSolrDocuments());

        // Trigger a reindex.
        $this->request->setMethod('POST');
        $this->dispatch('solr-search/reindex');

        // Should reindex the items.
        $this->_assertRecordInSolr($item1);
        $this->_assertRecordInSolr($item2);

    }


}
