<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class ResultsControllerTest_DisplayResults extends SolrSearch_Case_Default
{


    protected $_isAdminTest = false;


    /**
     * The current query should be populated in the search box.
     */
    public function testPopulateSearchBoxWithQuery()
    {

        $_GET['q'] = 'query';

        // Search for "query."
        $this->dispatch('solr-search');

        // Should populate the search box.
        $this->assertXpath('//input[@name="q"][@value="query"]');

    }


    /**
     * When an empty query is entered, the search box should be blank.
     */
    public function testPopulateSearchBoxWithEmptyQuery()
    {

        // Enter an empty query.
        $this->dispatch('solr-search');

        // Should leave the search box empty.
        $this->assertXpath('//input[@name="q"][@value=""]');

    }


}
