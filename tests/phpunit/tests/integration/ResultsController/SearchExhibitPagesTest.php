<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class ResultsControllerTest_SearchExhibitPages extends SolrSearch_Case_Default
{


    protected $_isAdminTest = false;


    /**
     * Install Exhibit Builder or skip the suite.
     */
    public function setUp()
    {
        parent::setUp();
        $this->_installPluginOrSkip('ExhibitBuilder');
    }


    /**
     * Search queries should match exhibit page titles.
     */
    public function testSearchTitles()
    {

        $exhibit1   = $this->_exhibit(true, 'Exhibit 1', 'e1');
        $exhibit2   = $this->_exhibit(true, 'Exhibit 2', 'e2');
        $page1      = $this->_exhibitPage($exhibit1, 'page1', 'p1');
        $page2      = $this->_exhibitPage($exhibit2, 'page2', 'p2');

        $_GET['q'] = 'page1';
        $this->dispatch('solr-search');

        // Should match page 1, but not page 2.
        $this->_assertResultLink(record_url($page1), 'page1');
        $this->_assertNotResultLink(record_url($page2));

    }


    /**
     * Search queries should match page entry texts.
     */
    public function testSearchEntries()
    {

        $exhibit1   = $this->_exhibit(true, 'Exhibit 1', 'e1');
        $exhibit2   = $this->_exhibit(true, 'Exhibit 2', 'e2');
        $page1      = $this->_exhibitPage($exhibit1, 'Page 1', 'p1');
        $page2      = $this->_exhibitPage($exhibit2, 'Page 2', 'p2');
        $entry1     = $this->_exhibitBlock($page1, 'text1');
        $entry2     = $this->_exhibitBlock($page1, 'text2');

        $_GET['q'] = 'text1';
        $this->dispatch('solr-search');

        // Should match page 1, but not page 2.
        $this->_assertResultLink(record_url($page1), 'Page 1');
        $this->_assertNotResultLink(record_url($page2));

    }


}
