<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class SolrSearchPluginTest_Items extends SolrSearch_Case_Default
{


    /**
     * When a new public item is added, it should be indexed in Solr.
     */
    public function testIndexNewPublicItem()
    {

        // Insert public item.
        $item = insert_item(array('public' => true));

        // Should add a Solr document.
        $this->_assertRecordInSolr($item);

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
        $this->_assertRecordInSolr($item);

    }


    /**
     * When a new private item is added, it should not be indexed in Solr.
     */
    public function testDontIndexNewPrivateItem()
    {

        // Insert private item.
        $item = insert_item(array('public' => false));

        // Should not add a Solr document.
        $this->_assertNotRecordInSolr($item);

    }


    /**
     * When an existing item is switched from public to private, it should be
     * removed from Solr.
     */
    public function testRemoveItemWhenSetPrivate()
    {

        // Insert public item.
        $item = insert_item(array('public' => true));

        // Set the item private.
        update_item($item, array('public' => false));

        // Should remove the Solr document.
        $this->_assertNotRecordInSolr($item);

    }


    /**
     * When an existing item is switched from public to private, it should be
     * removed from Solr.
     */
    public function testRemoveItemWhenDeleted()
    {

        // Insert public item.
        $item = insert_item(array('public' => true));

        // Delete.
        $item->delete();

        // Should remove the Solr document.
        $this->_assertNotRecordInSolr($item);

    }


    /**
     * The item URL should be indexed.
     */
    public function testIndexUrl()
    {

        // Add an item to the index.
        $item = insert_item(array('public' => true));

        // Get the Solr document for the item.
        $document = $this->_getRecordDocument($item);

        // Should index the URL.
        $this->assertEquals(record_url($item, 'show'), $document->url);

    }


    /**
     * The result type should be indexed.
     */
    public function testIndexResultType()
    {

        // Add an item to the index.
        $item = insert_item(array('public' => true));

        // Get the Solr document for the item.
        $document = $this->_getRecordDocument($item);

        // Should index the result type.
        $this->assertEquals('Item', $document->resulttype);

    }


    /**
     * The Dublin Core title should be indexed.
     */
    public function testIndexTitle()
    {

        // Add an item with a Dublin Core "Title."
        $item = insert_item(array('public' => true), array(
            'Dublin Core' => array (
                'Title' => array(
                    array('text' => 'title', 'html' => false)
                )
            )
        ));

        // Get the Solr document for the item.
        $document = $this->_getRecordDocument($item);

        // Should index the item type.
        $this->assertEquals('title', $document->title);

    }


    /**
     * The item type should be indexed.
     */
    public function testIndexItemType()
    {

        // Add an item of type "Software".
        $item = insert_item(array(
            'public' => true, 'item_type_name' => 'Software'
        ));

        // Get the Solr document for the item.
        $document = $this->_getRecordDocument($item);

        // Should index the item type.
        $this->assertEquals('Software', $document->itemtype);

    }


    /**
     * The collection title should be indexed.
     */
    public function testIndexCollection()
    {

        // Add collection with a "Title" element.
        $collection = insert_collection(array(), array(
            'Dublin Core' => array (
                'Title' => array(
                    array('text' => 'collection', 'html' => false)
                )
            )
        ));

        // Add an item to the collection.
        $item = insert_item(array(
            'public' => true, 'collection_id' => $collection->id
        ));

        // Get the Solr document for the item.
        $document = $this->_getRecordDocument($item);

        // Should index the collection title.
        $this->assertEquals('collection', $document->collection);

    }


    /**
     * The tags should be indexed.
     */
    public function testIndexTags()
    {

        // Add an item with tags.
        $item = insert_item(array(
            'public' => true, 'tags' => 'tag1,tag2,tag3'
        ));

        // Get the Solr document for the item.
        $document = $this->_getRecordDocument($item);

        // Should index the tags.
        $this->assertEquals(array('tag1', 'tag2', 'tag3'), $document->tag);

    }


    /**
     * Fields that have been marked as searchable should be indexed.
     */
    public function testIndexSearchableFields()
    {

        // Set "Subject" and "Source" searchable.
        $this->facetTable->setElementIndexed('Dublin Core', 'Subject');
        $this->facetTable->setElementIndexed('Dublin Core', 'Source');

        // Add an item with a "Subject" and "Source" texts.
        $item = insert_item(array('public' => true), array(
            'Dublin Core' => array (
                'Subject' => array(
                    array('text' => 'subject', 'html' => false)
                ),
                'Source' => array(
                    array('text' => 'source', 'html' => false)
                )
            )
        ));

        // Get the Solr document for the item.
        $document = $this->_getRecordDocument($item);

        // Get the subject and source keys.
        $subjectKey = $this->_getElementSolrKey('Dublin Core', 'Subject');
        $sourceKey  = $this->_getElementSolrKey('Dublin Core', 'Source');

        // Should index the searchable fields.
        $this->assertEquals('subject',  $document->$subjectKey);
        $this->assertEquals('source',   $document->$sourceKey);

    }


    /**
     * Fields that have been marked as not searchable should not be indexed.
     */
    public function testDontIndexUnsearchableFields()
    {

        // Add an item with a "Subject" and "Source" texts.
        $item = insert_item(array('public' => true), array(
            'Dublin Core' => array (
                'Subject' => array(
                    array('text' => 'subject', 'html' => false)
                ),
                'Source' => array(
                    array('text' => 'source', 'html' => false)
                )
            )
        ));

        // Get the Solr document for the item.
        $document = $this->_getRecordDocument($item);

        // Get the subject and source keys.
        $subjectKey = $this->_getElementSolrKey('Dublin Core', 'Subject');
        $sourceKey  = $this->_getElementSolrKey('Dublin Core', 'Source');

        // Should not index the un-searchable fields.
        $this->assertObjectNotHasAttribute($subjectKey, $document);
        $this->assertObjectNotHasAttribute($sourceKey, $document);

    }


}
