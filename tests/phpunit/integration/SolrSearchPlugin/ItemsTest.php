<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class SolrSearchPluginTest_Items extends SolrSearch_Test_AppTestCase
{


    /**
     * When a new public item is added, it should be indexed in Solr.
     */
    public function testIndexNewPublicItem()
    {

        // Insert public item.
        $item = insert_item(array('public' => true));

        // Should add a Solr document.
        $this->_assertItemInSolr($item);

    }


    /**
     * When an existing item is switched from private to public, it should be 
     * indexed in Solr.
     */
    public function testIndexItemWhenSetPublic()
    {

        // Insert private item.
        $item = insert_item(array('public' => false));

        // Set the item public.
        update_item($item, array('public' => true));

        // Should add a Solr document.
        $this->_assertItemInSolr($item);

    }


    /**
     * When a new private item is added, it should not be indexed in Solr.
     */
    public function testDontIndexNewPrivateItem()
    {

        // Insert private item.
        $item = insert_item(array('public' => false));

        // Should not add a Solr document.
        $this->_assertNotItemInSolr($item);

    }


    /**
     * When an existing item is switched from public to private, it should be 
     * removed from Solr.
     */
    public function testRemoveItemWhenSetPrivate()
    {

        // Insert public item.
        $item = insert_item(array('public' => true));

        // Should add a Solr document.
        $this->_assertItemInSolr($item);

        // Set the item private.
        update_item($item, array('public' => false));

        // Should remove the Solr document.
        $this->_assertNotItemInSolr($item);

    }


    /**
     * The "Item" `resulttype` should be indexed.
     */
    public function testIndexResultType()
    {

        // Insert item, get the document.
        $item = insert_item(array('public' => true));
        $document = $this->_getItemDocument($item);

        // Should index the result type.
        $this->assertEquals('Item', $document->resulttype);

    }


    /**
     * The item URL should be indexed.
     */
    public function testIndexUrl()
    {

        // Insert item, get the document.
        $item = insert_item(array('public' => true));
        $document = $this->_getItemDocument($item);

        // Should index the result type.
        $this->assertEquals(record_url($item, 'show'), $document->url);

    }


}
