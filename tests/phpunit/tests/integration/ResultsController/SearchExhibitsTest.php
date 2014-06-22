<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class ResultsControllerTest_SearchExhibits extends SolrSearch_Case_Default
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
     * Search queries should match exhibit titles.
     */
    public function testSearchTitles()
    {

        $exhibit1 = $this->_exhibit(true, 'exhibit1', 'e1');
        $exhibit2 = $this->_exhibit(true, 'exhibit2', 'e2');

        $_GET['q'] = 'exhibit1';
        $this->dispatch('solr-search');

        // Should match exhibit 1, but not exhibit 2.
        $this->_assertResultLink(record_url($exhibit1), 'exhibit1');
        $this->_assertNotResultLink(record_url($exhibit2));

    }


    /**
     * Search queries should match exhibit descriptions.
     */
    public function testSearchDescriptions()
    {

        $exhibit1 = $this->_exhibit(true, 'Exhibit 1', 'e1');
        $exhibit2 = $this->_exhibit(true, 'Exhibit 2', 'e2');
        $exhibit1->description = 'desc1';
        $exhibit2->description = 'desc2';

        $exhibit1->save();
        $exhibit2->save();

        $_GET['q'] = 'desc1';
        $this->dispatch('solr-search');

        // Should match exhibit 1, but not exhibit 2.
        $this->_assertResultLink(record_url($exhibit1), 'Exhibit 1');
        $this->_assertNotResultLink(record_url($exhibit2));

    }


}
