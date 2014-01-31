<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class AdminControllerTest_Reindex extends SolrSearch_Test_AppTestCase
{


    /**
     * REINDX should clear the Solr index and reindex all records.
     */
    public function testMarkup()
    {

        // Add items.
        $item1 = insert_item();
        $item2 = insert_item();

        // Delete the Solr records.
        SolrSearch_Helpers_Index::deleteAll();

        // Trigger a reindex.
        $this->dispatch('solr-search/reindex');

        // TODO: Assert 2 documents.

    }


}
