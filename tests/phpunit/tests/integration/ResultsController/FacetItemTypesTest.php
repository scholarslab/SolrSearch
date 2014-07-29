<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class ResultsControllerTest_FacetItemTypes extends SolrSearch_Case_Default
{


    protected $_isAdminTest = false;


    /**
     * Insert an item of a given type.
     *
     * @param string $title The item title.
     * @param ItemType $type The item type.
     * @return Item
     */
    protected function _collitem($title, $type)
    {
        return insert_item(
            array(
                'item_type_id' => $type->id,
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
     * Create items.
     */
    public function setUp()
    {

        parent::setUp();

        $website = $this->itemTypeTable->findByName('Website');
        $dataset = $this->itemTypeTable->findByName('Dataset');

        $this->item1 = $this->_collitem('Item 1', $website);
        $this->item2 = $this->_collitem('Item 2', $website);
        $this->item3 = $this->_collitem('Item 3', $dataset);
        $this->item4 = $this->_collitem('Item 4', $dataset);

    }


    /**
     * When no facet is applied, all facet links and items should be listed.
     */
    public function testNoFacet()
    {

        $this->dispatch('solr-search');

        $websiteLink = $this->_getFacetLink('itemtype', 'Website');
        $datasetLink = $this->_getFacetLink('itemtype', 'Dataset');

        // Should display all facet links.
        $this->_assertFacetLink($websiteLink, 'Website');
        $this->_assertFacetLink($datasetLink, 'Dataset');

        // Should display all items.
        $this->_assertResultLink(record_url($this->item1), 'Item 1');
        $this->_assertResultLink(record_url($this->item2), 'Item 2');
        $this->_assertResultLink(record_url($this->item3), 'Item 3');
        $this->_assertResultLink(record_url($this->item4), 'Item 4');

    }


    /**
     * When the `Website` facet is applied, just the `Website` facet should be
     * linked and just items that collection should be displayed.
     */
    public function testWebsite()
    {

        $this->dispatch($this->_getFacetLink('itemtype', 'Website'));

        $websiteLink = $this->_getFacetLink('itemtype', 'Website');
        $datasetLink = $this->_getFacetLink('itemtype', 'Dataset');

        // Should remove the `Dataset` facet link.
        $this->_assertFacetLink($websiteLink, 'Website');
        $this->_assertNotFacetLink($datasetLink);

        // Should list items in `Website`.
        $this->_assertResultLink(record_url($this->item1), 'Item 1');
        $this->_assertResultLink(record_url($this->item2), 'Item 2');
        $this->_assertNotResultLink(record_url($this->item3));
        $this->_assertNotResultLink(record_url($this->item4));

    }


    /**
     * When the `Dataset` facet is applied, just the `Dataset` facet should be
     * linked and just items that collection should be displayed.
     */
    public function testDataset()
    {

        $this->dispatch($this->_getFacetLink('itemtype', 'Dataset'));

        $websiteLink = $this->_getFacetLink('itemtype', 'Website');
        $datasetLink = $this->_getFacetLink('itemtype', 'Dataset');

        // Should remove the `Website` facet link.
        $this->_assertNotFacetLink($websiteLink);
        $this->_assertFacetLink($datasetLink, 'Dataset');

        // Should list items in `Dataset`.
        $this->_assertNotResultLink(record_url($this->item1));
        $this->_assertNotResultLink(record_url($this->item2));
        $this->_assertResultLink(record_url($this->item3), 'Item 3');
        $this->_assertResultLink(record_url($this->item4), 'Item 4');

    }


}
