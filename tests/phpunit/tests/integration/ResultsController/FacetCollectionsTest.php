<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class ResultsControllerTest_FacetCollections extends SolrSearch_Case_Default
{


    protected $_isAdminTest = false;


    /**
     * Create a collection.
     *
     * @param string $title The collection title.
     * @return Collection
     */
    protected function _collection($title)
    {
        return insert_collection(
            array(
                'public' => true
            ),
            array(
                'Dublin Core' => array(
                    'Title' => array(
                        array('text' => $title, 'html' => false)
                    )
                )
            )
        );
    }


    /**
     * Insert an item into a collection.
     *
     * @param string $title The item title.
     * @param Collection $collection The parent collection.
     * @return Item
     */
    protected function _collitem($title, $collection)
    {
        return insert_item(
            array(
                'collection_id' => $collection->id,
                'public' => true
            ), array(
                'Dublin Core' => array(
                    'Title' => array(
                        array('text' => $title, 'html' => false)
                    )
                )
            )
        );
    }


    /**
     * Create collections and items.
     */
    public function setUp()
    {

        parent::setUp();

        $coll1 = $this->_collection('Collection 1');
        $coll2 = $this->_collection('Collection 2');

        $this->item1 = $this->_collitem('Item 1', $coll1);
        $this->item2 = $this->_collitem('Item 2', $coll1);
        $this->item3 = $this->_collitem('Item 3', $coll2);
        $this->item4 = $this->_collitem('Item 4', $coll2);

    }


    /**
     * When no facet is applied, all facet links and items should be listed.
     */
    public function testNoFacet()
    {

        $this->dispatch('solr-search');

        $coll1Link = $this->_getFacetLink('collection', 'Collection 1');
        $coll2Link = $this->_getFacetLink('collection', 'Collection 2');

        // Should display all facet links.
        $this->_assertFacetLink($coll1Link, 'Collection 1');
        $this->_assertFacetLink($coll2Link, 'Collection 2');

        // Should display all items.
        $this->_assertResultLink(record_url($this->item1), 'Item 1');
        $this->_assertResultLink(record_url($this->item2), 'Item 2');
        $this->_assertResultLink(record_url($this->item3), 'Item 3');
        $this->_assertResultLink(record_url($this->item4), 'Item 4');

    }


    /**
     * When the `Collection 1` facet is applied, just the `Collection 1` facet
     * should be linked and just items that collection should be displayed.
     */
    public function testCollection1()
    {

        $this->dispatch($this->_getFacetLink('collection', 'Collection 1'));

        $coll1Link = $this->_getFacetLink('collection', 'Collection 1');
        $coll2Link = $this->_getFacetLink('collection', 'Collection 2');

        // Should remove the `Collection 2` facet link.
        $this->_assertFacetLink($coll1Link, 'Collection 1');
        $this->_assertNotFacetLink($coll2Link);

        // Should list items in `Collection 1`.
        $this->_assertResultLink(record_url($this->item1), 'Item 1');
        $this->_assertResultLink(record_url($this->item2), 'Item 2');
        $this->_assertNotResultLink(record_url($this->item3));
        $this->_assertNotResultLink(record_url($this->item4));

    }


    /**
     * When the `Collection 2` facet is applied, just the `Collection 2` facet
     * should be linked and just items that collection should be displayed.
     */
    public function testCollection2()
    {

        $this->dispatch($this->_getFacetLink('collection', 'Collection 2'));

        $coll1Link = $this->_getFacetLink('collection', 'Collection 1');
        $coll2Link = $this->_getFacetLink('collection', 'Collection 2');

        // Should remove the `Collection 1` facet link.
        $this->_assertNotFacetLink($coll1Link);
        $this->_assertFacetLink($coll2Link, 'Collection 2');

        // Should list items in `Collection 2`.
        $this->_assertNotResultLink(record_url($this->item1));
        $this->_assertNotResultLink(record_url($this->item2));
        $this->_assertResultLink(record_url($this->item3), 'Item 3');
        $this->_assertResultLink(record_url($this->item4), 'Item 4');

    }


}
