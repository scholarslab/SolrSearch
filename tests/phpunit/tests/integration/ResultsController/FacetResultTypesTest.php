<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class ResultsControllerTest_FacetResultTypes extends SolrSearch_Case_Default
{


    protected $_isAdminTest = false;


    /**
     * Create collections and items.
     */
    public function setUp()
    {

        parent::setUp();

        $this->_installPluginOrSkip('ExhibitBuilder');
        $this->_installPluginOrSkip('SimplePages');

        $this->i1  = $this->_item(true, 'Item 1');
        $this->i2  = $this->_item(true, 'Item 2');
        $this->e1  = $this->_exhibit(true, 'Exhibit 1', 'e1');
        $this->e2  = $this->_exhibit(true, 'Exhibit 2', 'e2');
        $this->ep1 = $this->_exhibitPage($this->e1, 'Exhibit Page 1', 'ep1');
        $this->ep2 = $this->_exhibitPage($this->e1, 'Exhibit Page 2', 'ep2');
        $this->sp1 = $this->_simplePage(true, 'Simple Page 1', 'sp1');
        $this->sp2 = $this->_simplePage(true, 'Simple Page 2', 'sp2');

    }


    /**
     * When no facet is applied, all facet links and items should be listed.
     */
    public function testNoFacet()
    {

        $this->dispatch('solr-search');

        $iLink  = $this->_getFacetLink('resulttype', 'Item');
        $eLink  = $this->_getFacetLink('resulttype', 'Exhibit');
        $epLink = $this->_getFacetLink('resulttype', 'Exhibit Page');
        $spLink = $this->_getFacetLink('resulttype', 'Simple Page');

        // Should display all facet links.
        $this->_assertFacetLink($iLink,  'Item');
        $this->_assertFacetLink($eLink,  'Exhibit');
        $this->_assertFacetLink($epLink, 'Exhibit Page');
        $this->_assertFacetLink($spLink, 'Simple Page');

        // Should display all records.
        $this->_assertResultLink(record_url($this->i1),  'Item 1');
        $this->_assertResultLink(record_url($this->i2),  'Item 2');
        $this->_assertResultLink(record_url($this->e1),  'Exhibit 1');
        $this->_assertResultLink(record_url($this->e2),  'Exhibit 2');
        $this->_assertResultLink(record_url($this->ep1), 'Exhibit Page 1');
        $this->_assertResultLink(record_url($this->ep2), 'Exhibit Page 2');
        $this->_assertResultLink(record_url($this->sp1), 'Simple Page 1');
        $this->_assertResultLink(record_url($this->sp2), 'Simple Page 2');

    }


    /**
     * When the `Item` facet is applied, just the `Item` facet link should be
     * listed and just the item records should be displayed.
     */
    public function testItem()
    {

        $this->dispatch($this->_getFacetLink('resulttype', 'Item'));

        $iLink  = $this->_getFacetLink('resulttype', 'Item');
        $eLink  = $this->_getFacetLink('resulttype', 'Exhibit');
        $epLink = $this->_getFacetLink('resulttype', 'Exhibit Page');
        $spLink = $this->_getFacetLink('resulttype', 'Simple Page');

        // Should just display the `Item` link.
        $this->_assertFacetLink($iLink, 'Item');
        $this->_assertNotFacetLink($eLink);
        $this->_assertNotFacetLink($epLink);
        $this->_assertNotFacetLink($spLink);

        // Should just display the items.
        $this->_assertResultLink(record_url($this->i1), 'Item 1');
        $this->_assertResultLink(record_url($this->i2), 'Item 2');
        $this->_assertNotResultLink(record_url($this->e1));
        $this->_assertNotResultLink(record_url($this->e2));
        $this->_assertNotResultLink(record_url($this->ep1));
        $this->_assertNotResultLink(record_url($this->ep2));
        $this->_assertNotResultLink(record_url($this->sp1));
        $this->_assertNotResultLink(record_url($this->sp2));

    }


    /**
     * When the `Exhibit` facet is applied, just the `Exhibit` facet link
     * should be listed and just the item records should be displayed.
     */
    public function testExhibit()
    {

        $this->dispatch($this->_getFacetLink('resulttype', 'Exhibit'));

        $iLink  = $this->_getFacetLink('resulttype', 'Item');
        $eLink  = $this->_getFacetLink('resulttype', 'Exhibit');
        $epLink = $this->_getFacetLink('resulttype', 'Exhibit Page');
        $spLink = $this->_getFacetLink('resulttype', 'Simple Page');

        // Should just display the `Exhibit` link.
        $this->_assertNotFacetLink($iLink);
        $this->_assertFacetLink($eLink, 'Exhibit');
        $this->_assertNotFacetLink($epLink);
        $this->_assertNotFacetLink($spLink);

        // Should just display the items.
        $this->_assertNotResultLink(record_url($this->i1));
        $this->_assertNotResultLink(record_url($this->i2));
        $this->_assertResultLink(record_url($this->e1), 'Exhibit 1');
        $this->_assertResultLink(record_url($this->e2), 'Exhibit 2');
        $this->_assertNotResultLink(record_url($this->ep1));
        $this->_assertNotResultLink(record_url($this->ep2));
        $this->_assertNotResultLink(record_url($this->sp1));
        $this->_assertNotResultLink(record_url($this->sp2));

    }


    /**
     * When the `Exhibit Page` facet is applied, just the `Exhibit Page` facet
     * link should be listed and just the item records should be displayed.
     */
    public function testExhibitPage()
    {

        $this->dispatch($this->_getFacetLink('resulttype', 'Exhibit Page'));

        $iLink  = $this->_getFacetLink('resulttype', 'Item');
        $eLink  = $this->_getFacetLink('resulttype', 'Exhibit');
        $epLink = $this->_getFacetLink('resulttype', 'Exhibit Page');
        $spLink = $this->_getFacetLink('resulttype', 'Simple Page');

        // Should just display the `Exhibit Page` link.
        $this->_assertNotFacetLink($iLink);
        $this->_assertNotFacetLink($eLink);
        $this->_assertFacetLink($epLink, 'Exhibit Page');
        $this->_assertNotFacetLink($spLink);

        // Should just display the items.
        $this->_assertNotResultLink(record_url($this->i1));
        $this->_assertNotResultLink(record_url($this->i2));
        $this->_assertNotResultLink(record_url($this->e1));
        $this->_assertNotResultLink(record_url($this->e2));
        $this->_assertResultLink(record_url($this->ep1), 'Exhibit Page 1');
        $this->_assertResultLink(record_url($this->ep2), 'Exhibit Page 2');
        $this->_assertNotResultLink(record_url($this->sp1));
        $this->_assertNotResultLink(record_url($this->sp2));

    }


    /**
     * When the `Simple Page` facet is applied, just the `Simple Page` facet
     * link should be listed and just the item records should be displayed.
     */
    public function testSimplePage()
    {

        $this->dispatch($this->_getFacetLink('resulttype', 'Simple Page'));

        $iLink  = $this->_getFacetLink('resulttype', 'Item');
        $eLink  = $this->_getFacetLink('resulttype', 'Exhibit');
        $epLink = $this->_getFacetLink('resulttype', 'Exhibit Page');
        $spLink = $this->_getFacetLink('resulttype', 'Simple Page');

        // Should just display the `Simple Page` link.
        $this->_assertNotFacetLink($iLink);
        $this->_assertNotFacetLink($eLink);
        $this->_assertNotFacetLink($epLink);
        $this->_assertFacetLink($spLink, 'Simple Page');

        // Should just display the items.
        $this->_assertNotResultLink(record_url($this->i1));
        $this->_assertNotResultLink(record_url($this->i2));
        $this->_assertNotResultLink(record_url($this->e1));
        $this->_assertNotResultLink(record_url($this->e2));
        $this->_assertNotResultLink(record_url($this->ep1));
        $this->_assertNotResultLink(record_url($this->ep2));
        $this->_assertResultLink(record_url($this->sp1), 'Simple Page 1');
        $this->_assertResultLink(record_url($this->sp2), 'Simple Page 2');

    }


}
