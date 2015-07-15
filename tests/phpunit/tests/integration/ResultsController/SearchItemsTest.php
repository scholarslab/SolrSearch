<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class ResultsControllerTest_SearchItems extends SolrSearch_Case_Default
{


    protected $_isAdminTest = false;


    /**
     * Search queries should match item titles.
     */
    public function testSearchTitles()
    {

        $item1 = insert_item(array('public' => true), array(
            'Dublin Core' => array(
                'Title' => array(
                    array('text' => 'item1', 'html' => false)
                )
            )
        ));

        $item2 = insert_item(array('public' => true), array(
            'Dublin Core' => array(
                'Title' => array(
                    array('text' => 'item2', 'html' => false)
                )
            )
        ));

        $_GET['q'] = 'item1';
        $this->dispatch('solr-search');

        // Should match item 1, but not item 2.
        $this->_assertResultLink(record_url($item1), 'item1');
        $this->_assertNotResultLink(record_url($item2));

    }


    /**
     * Search queries should match indexed elements.
     */
    public function testSearchIndexedElements()
    {

        $this->fieldTable->setElementIndexed('Dublin Core', 'Description');

        $item1 = insert_item(array('public' => true), array(
            'Dublin Core' => array(
                'Title' => array(
                    array('text' => 'Item 1', 'html' => false)
                ),
                'Description' => array(
                    array('text' => 'desc1', 'html' => false)
                )
            )
        ));

        $item2 = insert_item(array('public' => true), array(
            'Dublin Core' => array(
                'Title' => array(
                    array('text' => 'Item 2', 'html' => false)
                ),
                'Description' => array(
                    array('text' => 'desc2', 'html' => false)
                )
            )
        ));

        $_GET['q'] = 'desc1';
        $this->dispatch('solr-search');

        // Should match item 1, but not item 2.
        $this->_assertResultLink(record_url($item1), 'Item 1');
        $this->_assertNotResultLink(record_url($item2));

    }

    /**
     * Special character handling (#119)
     */
    public function testSpecialCharHandling()
    {
        $this->fieldTable->setElementIndexed('Dublin Core', 'Description');

        $_GET['q'] = 'hop[e]';
        $this->dispatch('solr-search');

        $this->assertTrue(TRUE);
    }

}
