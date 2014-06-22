<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class ResultsControllerTest_HighlightResults extends SolrSearch_Case_Default
{


    protected $_isAdminTest = false;


    /**
     * Create collections and items.
     */
    public function setUp()
    {
        parent::setUp();
        $this->item1 = $this->_item(true, 'Item 1');
        $this->item2 = $this->_item(true, 'Item 2');
    }


    /**
     * When hit highlighting is enabled, snippets should be displayed.
     */
    public function testHighlightingEnabled()
    {

        // Enable highlighting.
        set_option('solr_search_hl', '1');

        // Search for "item".
        $_GET['q'] = 'item';
        $this->dispatch('solr-search');

        // Should highlight item 1.
        $this->assertXpathContentContains(
            '//li[@class="snippet"]', '<em>Item</em> 1'
        );

        // Should highlight item 2.
        $this->assertXpathContentContains(
            '//li[@class="snippet"]', '<em>Item</em> 1'
        );

    }


    /**
     * When hit highlighting is disabled, snippets should not be displayed.
     */
    public function testHighlightingDisabled()
    {

        // Disable highlighting.
        set_option('solr_search_hl', '0');

        // Search for "item".
        $_GET['q'] = 'item';
        $this->dispatch('solr-search');

        // Should not show highlights.
        $this->assertNotXpath('//li[@class="snippet"]');

    }


}
