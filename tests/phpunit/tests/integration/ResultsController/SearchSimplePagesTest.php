<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class ResultsControllerTest_SearchSimplePages extends SolrSearch_Case_Default
{


    protected $_isAdminTest = false;


    /**
     * Install Simple Pages or skip the suite.
     */
    public function setUp()
    {
        parent::setUp();
        $this->_installPluginOrSkip('SimplePages');
    }


    /**
     * Search queries should match page titles.
     */
    public function testSearchTitles()
    {

        $page1 = $this->_simplePage(true, 'page1', 'p1');
        $page2 = $this->_simplePage(true, 'page2', 'p2');

        $_GET['q'] = 'page1';
        $this->dispatch('solr-search');

        // Should match page 1, but not page 2.
        $this->_assertResultLink(record_url($page1), 'page1');
        $this->_assertNotResultLink(record_url($page2));

    }


    /**
     * Search queries should match page descriptions.
     */
    public function testSearchDescriptions()
    {

        $page1 = $this->_simplePage(true, 'Page 1', 'p1');
        $page2 = $this->_simplePage(true, 'Page 2', 'p2');
        $page1->text = 'text1';
        $page2->text = 'text2';

        $page1->save();
        $page2->save();

        $_GET['q'] = 'text1';
        $this->dispatch('solr-search');

        // Should match page 1, but not page 2.
        $this->_assertResultLink(record_url($page1), 'Page 1');
        $this->_assertNotResultLink(record_url($page2));

    }


}
