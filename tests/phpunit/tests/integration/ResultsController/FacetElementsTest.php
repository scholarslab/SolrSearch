<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class ResultsControllerTest_FacetElements extends SolrSearch_Case_Default
{


    protected $_isAdminTest = false;


    /**
     * Insert an item into a collection.
     *
     * @param string $title The Dublin Core "Title".
     * @param string $type The Dublin Core "Type".
     * @return Item
     */
    protected function _collitem($title, $type)
    {
        return insert_item(
            array(
                'public' => true
            ), array(
                'Dublin Core' => array(
                    'Title' => array(
                        array('text' => $title, 'html' => false)
                    ),
                    'Type' => array(
                        array('text' => $type, 'html' => false)
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

        // Set "Type" faceted.
        $this->fieldTable->setElementFaceted('Dublin Core', 'Type');

        // Cache the "Type" facet key.
        $type = $this->fieldTable->findByElementName('Dublin Core', 'Type');
        $this->key = $type->facetKey();

        $this->item1 = $this->_collitem('Item 1', 'type one');
        $this->item2 = $this->_collitem('Item 2', 'type one');
        $this->item3 = $this->_collitem('Item 3', 'type two');
        $this->item4 = $this->_collitem('Item 4', 'type two');

    }


    /**
     * When no facet is applied, all facet links and items should be listed.
     */
    public function testNoFacet()
    {

        $this->dispatch('solr-search');

        $type1Link = $this->_getFacetLink($this->key, 'type one');
        $type2Link = $this->_getFacetLink($this->key, 'type two');

        // Should display all facet links.
        $this->_assertFacetLink($type1Link, 'type one');
        $this->_assertFacetLink($type2Link, 'type two');

        // Should display all items.
        $this->_assertResultLink(record_url($this->item1), 'Item 1');
        $this->_assertResultLink(record_url($this->item2), 'Item 2');
        $this->_assertResultLink(record_url($this->item3), 'Item 3');
        $this->_assertResultLink(record_url($this->item4), 'Item 4');

    }


    /**
     * When the `type one` facet is applied, just the `type one` facet should
     * be linked and just items of that type should be displayed.
     */
    public function testTypeOne()
    {

        $this->dispatch($this->_getFacetLink($this->key, 'type one'));

        $type1Link = $this->_getFacetLink($this->key, 'type one');
        $type2Link = $this->_getFacetLink($this->key, 'type two');

        // Should remove the `type two` facet link.
        $this->_assertFacetLink($type1Link, 'type one');
        $this->_assertNotFacetLink($type2Link);

        // Should list items with `type one`.
        $this->_assertResultLink(record_url($this->item1), 'Item 1');
        $this->_assertResultLink(record_url($this->item2), 'Item 2');
        $this->_assertNotResultLink(record_url($this->item3));
        $this->_assertNotResultLink(record_url($this->item4));

    }


    /**
     * When the `type two` facet is applied, just the `type two` facet should
     * be linked and just items of that type should be displayed.
     */
    public function testTypeTwo()
    {

        $this->dispatch($this->_getFacetLink($this->key, 'type two'));

        $type1Link = $this->_getFacetLink($this->key, 'type one');
        $type2Link = $this->_getFacetLink($this->key, 'type two');

        // Should remove the `type two` facet link.
        $this->_assertNotFacetLink($type1Link);
        $this->_assertFacetLink($type2Link, 'type two');

        // Should list items with `type two`.
        $this->_assertNotResultLink(record_url($this->item1));
        $this->_assertNotResultLink(record_url($this->item2));
        $this->_assertResultLink(record_url($this->item3), 'Item 3');
        $this->_assertResultLink(record_url($this->item4), 'Item 4');

    }


}
