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


    /**
     * When a new private item is added, it should not be indexed in Solr.
     */
    public function testNoDisplayPrivate()
    {
        $opt = get_option('solr_search_display_private_items');
        set_option('solr_search_display_private_items', '0');

        try {
            $item = insert_item(
                array('public' => false),
                array(
                    'Dublin Core' => array(
                        'Title' => array(
                            array('text' => 'testNoDisplayPrivate', 'html' => false)
                        )
                    )
                )
            );
            $this->_assertRecordInSolr($item);

            $_GET['q'] = 'testNoDisplayPrivate';
            $this->dispatch('solr-search');
            $this->assertNotQueryContentContains('.result-title', 'testNoDisplayPrivate');

        } catch (Exception $e) {
            throw $e;

        } finally {
            set_option('solr_search_display_private_items', $opt);
        }


    }


}
