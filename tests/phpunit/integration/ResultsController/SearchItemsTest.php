<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class ResultsControllerTest_SearchItems extends SolrSearch_Test_AppTestCase
{


    protected $_isAdminTest = false;


    /**
     * Item results should show the item titles.
     */
    public function testDisplayTitles()
    {

        $item1 = insert_item(array('public' => true), array(
            'Dublin Core' => array (
                'Title' => array(
                    array('text' => 'Item 1', 'html' => false)
                )
            )
        ));

        $item2 = insert_item(array('public' => true), array(
            'Dublin Core' => array (
                'Title' => array(
                    array('text' => 'Item 2', 'html' => false)
                )
            )
        ));

        $item3 = insert_item(array('public' => true), array(
            'Dublin Core' => array (
                'Title' => array(
                    array('text' => 'Item 3', 'html' => false)
                )
            )
        ));

        // Search for all documents.
        $this->dispatch('solr-search/results');

        // Should display item titles.
        $this->assertXpathContentContains(
            '//a[@href="'.record_url($item1).'"]', 'Item 1'
        );
        $this->assertXpathContentContains(
            '//a[@href="'.record_url($item2).'"]', 'Item 2'
        );
        $this->assertXpathContentContains(
            '//a[@href="'.record_url($item3).'"]', 'Item 3'
        );

    }


}
