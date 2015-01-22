<?php

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
    public function testIndexNewPrivateItem()
    {

        // Insert private item.
        $item = insert_item(array('public' => false));

        // Should not add a Solr document.
        $this->_assertRecordInSolr($item);

    }


    /**
     * When an existing item is switched from public to private, it should be
     * removed from Solr.
     */
    public function testDontRemoveItemWhenSetPrivate()
    {

        // Insert public item.
        $item = insert_item(array('public' => true));

        // Set the item private.
        update_item($item, array('public' => false));

        // Should remove the Solr document.
        $this->_assertRecordInSolr($item);

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
     * Indexed fields should be stored as text fields.
     */
    public function testSetTextFieldsForIndexedElements()
    {

        // Set "Format" and "Source" indexed.
        $this->fieldTable->setElementIndexed('Dublin Core', 'Format', true);
        $this->fieldTable->setElementIndexed('Dublin Core', 'Source', true);

        // Add an item with a "Format" and "Source" texts.
        $item = insert_item(array('public' => true), array(
            'Dublin Core' => array (
                'Format' => array(
                    array('text' => 'format', 'html' => false)
                ),
                'Source' => array(
                    array('text' => 'source', 'html' => false)
                )
            )
        ));

        // Get the Solr document for the item.
        $document = $this->_getRecordDocument($item);

        // Get the "Format" and "Source" keys.
        $formatKey = $this->_getElementTextKey('Dublin Core', 'Format');
        $sourceKey = $this->_getElementTextKey('Dublin Core', 'Source');

        // Should index the searchable fields.
        $this->assertEquals('format', $document->$formatKey);
        $this->assertEquals('source', $document->$sourceKey);

    }


    /**
     * Un-indexed fields should not be stored as text fields.
     */
    public function testDontSetTextFieldsForUnindexedElements()
    {

        // Set "Format" and "Source" un-indexed.
        $this->fieldTable->setElementIndexed('Dublin Core', 'Format', false);
        $this->fieldTable->setElementIndexed('Dublin Core', 'Source', false);

        // Add an item with a "Format" and "Source" texts.
        $item = insert_item(array('public' => true), array(
            'Dublin Core' => array (
                'Format' => array(
                    array('text' => 'format', 'html' => false)
                ),
                'Source' => array(
                    array('text' => 'source', 'html' => false)
                )
            )
        ));

        // Get the Solr document for the item.
        $document = $this->_getRecordDocument($item);

        // Get the "Format" and "Source" keys.
        $formatKey = $this->_getElementTextKey('Dublin Core', 'Format');
        $sourceKey = $this->_getElementTextKey('Dublin Core', 'Source');

        // Should not index the un-searchable fields.
        $this->assertObjectNotHasAttribute($formatKey, $document);
        $this->assertObjectNotHasAttribute($sourceKey, $document);

    }


    /**
     * Faceted fields should be stored as string fields.
     */
    public function testSetStringFieldsForFacetedElements()
    {

        // Set "Format" and "Source" faceted.
        $this->fieldTable->setElementFaceted('Dublin Core', 'Format', true);
        $this->fieldTable->setElementFaceted('Dublin Core', 'Source', true);

        // Add an item with a "Format" and "Source" texts.
        $item = insert_item(array('public' => true), array(
            'Dublin Core' => array (
                'Format' => array(
                    array('text' => 'format', 'html' => false)
                ),
                'Source' => array(
                    array('text' => 'source', 'html' => false)
                )
            )
        ));

        // Get the Solr document for the item.
        $document = $this->_getRecordDocument($item);

        // Get the "Format" and "Source" keys.
        $formatKey = $this->_getElementStringKey('Dublin Core', 'Format');
        $sourceKey = $this->_getElementStringKey('Dublin Core', 'Source');

        // Should index the faceted fields.
        $this->assertEquals('format', $document->$formatKey);
        $this->assertEquals('source', $document->$sourceKey);

    }


    /**
     * Un-faceted fields should not be stored as string fields.
     */
    public function testDontSetStringFieldsForUnfacetedElements()
    {

        // Set "Format" and "Source" un-faceted.
        $this->fieldTable->setElementFaceted('Dublin Core', 'Format', false);
        $this->fieldTable->setElementFaceted('Dublin Core', 'Source', false);

        // Add an item with a "Format" and "Source" texts.
        $item = insert_item(array('public' => true), array(
            'Dublin Core' => array (
                'Format' => array(
                    array('text' => 'format', 'html' => false)
                ),
                'Source' => array(
                    array('text' => 'source', 'html' => false)
                )
            )
        ));

        // Get the Solr document for the item.
        $document = $this->_getRecordDocument($item);

        // Get the "Format" and "Source" keys.
        $formatKey = $this->_getElementStringKey('Dublin Core', 'Format');
        $sourceKey = $this->_getElementStringKey('Dublin Core', 'Source');

        // Should not index the un-faceted fields.
        $this->assertObjectNotHasAttribute($formatKey, $document);
        $this->assertObjectNotHasAttribute($sourceKey, $document);

    }

    public function testIndexFeatured()
    {
        $item = insert_item(
            array('featured' => true, 'public' => true),
            array('Dublin Core' => array(
                'Title' => array(
                    array('text' => 'test index featured', 'html' => false)
            ))
        ));
        $doc = $this->_getRecordDocument($item);
        $this->assertEquals(true, $doc->featured);
    }

}
