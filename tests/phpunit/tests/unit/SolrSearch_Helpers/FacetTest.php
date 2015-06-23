<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2015 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class SolrSearch_Helpers_Facet_Test extends SolrSearch_Case_Default
{

    public function testMakeUrlEncode()
    {
        $facets = array(
            array('49_s', 'Buildings, Cities & towns, Streets')
        );
        $this->assertEquals(
            '/solr-search?q=&amp;facet=' .
            '49_s%3A%22Buildings%2C+Cities+%26+towns%2C+Streets%22',
            SolrSearch_Helpers_Facet::makeUrl($facets)
        );
    }
}

