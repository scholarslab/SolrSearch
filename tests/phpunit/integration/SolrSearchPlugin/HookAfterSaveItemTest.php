<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class SolrSearchPluginTest_HookAfterSaveItem
    extends SolrSearch_Test_AppTestCase
{


    /**
     * Search for an item document in Solr.
     *
     * @param Item $item The Omeka item.
     * @return Apache_Solr_Response
     */
    private function _searchForItem($item)
    {

        // Get a Solr document for the item.
        $doc = SolrSearch_Helpers_Index::itemToDocument($this->db, $item);
        $id = $doc->getField('id');

        // Query for the document.
        return $this->solr->search("id:{$id['value']}");

    }


    /**
     * Assert that an item is indexed in Solr.
     *
     * @param Item $item The Omeka item.
     */
    private function _assertSolrDocument($item)
    {

        // Query for the document.
        $result = $this->_searchForItem($item);

        // Solr document should exist.
        $this->assertEquals(1, $result->response->numFound);

    }


    /**
     * Assert that an item is _not_ indexed in Solr.
     *
     * @param Item $item The Omeka item.
     */
    private function _assertNotSolrDocument($item)
    {

        // Query for the document.
        $result = $this->_searchForItem($item);

        // Solr document should not exist.
        $this->assertEquals(0, $result->response->numFound);

    }


    /**
     * When a new public item is added, it should be indexed in Solr.
     */
    public function testIndexNewPublicItem()
    {

        // Connect to Solr, insert public item.
        $solr = SolrSearch_Helpers_Index::connect();
        $item = insert_item(array('public' => true));

        // Should add a Solr document.
        $this->_assertSolrDocument($item);

    }


    /**
     * When an existing item is switched from private to public, it should be 
     * indexed in Solr.
     */
    public function testIndexItemWhenSetPublic()
    {

        // Connect to Solr, insert private item.
        $solr = SolrSearch_Helpers_Index::connect();
        $item = insert_item(array('public' => false));

        // Set the item public.
        update_item($item, array('public' => true));

        // Should add a Solr document.
        $this->_assertSolrDocument($item);

    }


    /**
     * When a new private item is added, it should not be indexed in Solr.
     */
    public function testDontIndexNewPrivateItem()
    {

        // Connect to Solr, insert private item.
        $solr = SolrSearch_Helpers_Index::connect();
        $item = insert_item(array('public' => false));

        // Should add a Solr document.
        $this->_assertNotSolrDocument($item);

    }


    /**
     * When an existing item is switched from public to private, it should be 
     * removed from Solr.
     */
    public function testRemoveItemWhenSetPrivate()
    {

        // Connect to Solr, insert public item.
        $solr = SolrSearch_Helpers_Index::connect();
        $item = insert_item(array('public' => true));

        // Set the item private.
        update_item($item, array('public' => false));

        // Should add a Solr document.
        $this->_assertNotSolrDocument($item);

    }


}
