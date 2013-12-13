<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class AdminControllerTest_Highlighting extends SolrSearch_Test_AppTestCase
{


    /**
     * INDEX should display the hit highlighting form.
     */
    public function testIndex()
    {

        $this->dispatch('solr-search/highlighting');

        // Highlighting:
        $this->assertXpath(
            '//select
            [@name="solr_search_hl"]
            /option
            [@value="'. get_option('solr_search_hl') . '"]
            [@selected="selected"]'
        );

        // Snippets:
        $this->assertXpath(
            '//input
            [@name="solr_search_snippets"]
            [@value="' . get_option('solr_search_snippets') . '"]'
        );

        // Snippet size:
        $this->assertXpath(
            '//input
            [@name="solr_search_fragsize"]
            [@value="' . get_option('solr_search_fragsize') . '"]'
        );

    }


}
