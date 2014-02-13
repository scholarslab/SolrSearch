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
     * The item URL should be indexed.
     */
    public function testIndexUrl()
    {

        $item = insert_item(array('public' => true));
        $document = $this->_getItemDocument($item);

        // Should index the result type.
        $this->assertEquals(record_url($item, 'show'), $document->url);

    }


    /**
     * The "Item" `resulttype` should be indexed.
     */
    public function testIndexResultType()
    {

        $item = insert_item(array('public' => true));
        $document = $this->_getItemDocument($item);

        // Should index the result type.
        $this->assertEquals('Item', $document->resulttype);

    }


    /**
     * The Dublin Core title should be indexed.
     */
    public function testIndexTitle()
    {

        $item = insert_item(array('public' => true), array(
            'Dublin Core' => array (
                'Title' => array(
                    array('text' => 'Test Title', 'html' => false)
                )
            )
        ));

        $document = $this->_getItemDocument($item);

        // Should index the item type.
        $this->assertEquals('Test Title', $document->title);

    }


    /**
     * The item type should be indexed.
     */
    public function testIndexItemType()
    {

        $item = insert_item(array(
            'item_type_name' => 'Software',
            'public' => true
        ));

        $document = $this->_getItemDocument($item);

        // Should index the item type.
        $this->assertEquals('Software', $document->itemtype);

    }


    /**
     * The collection title should be indexed.
     */
    public function testIndexCollection()
    {

        $collection = insert_collection(array(), array(
            'Dublin Core' => array (
                'Title' => array(
                    array('text' => 'Test Collection', 'html' => false)
                )
            )
        ));

        $item = insert_item(array(
            'collection_id' => $collection->id,
            'public' => true
        ));

        $document = $this->_getItemDocument($item);

        // Should index the collection title.
        $this->assertEquals('Test Collection', $document->collection);

    }


    /**
     * The tags should be indexed.
     */
    public function testIndexTags()
    {

        $item = insert_item(array(
            'tags' => 'tag1,tag2,tag3',
            'public' => true
        ));

        $document = $this->_getItemDocument($item);

        // Should index the tags.
        $this->assertEquals(array('tag1', 'tag2', 'tag3'), $document->tag);

    }


}
