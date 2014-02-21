<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTableTest_GetActiveFacetNames
    extends SolrSearch_Case_Default
{


    /**
     * Delete any facet mappings registered when the plugin is installed.
     */
    public function setUp()
    {
        parent::setUp();
        $this->_clearFacetMappings();
    }


    /**
     * `getActiveFacetNames` should return a list containing the names of all
     * the facets that are set active.
     * @group names
     */
    public function testGetActiveFacetNames()
    {

        $facet1 = $this->_facet('facet1');
        $facet2 = $this->_facet('facet2');
        $facet3 = $this->_facet('facet3');

        $facet1->is_facet = true;
        $facet2->is_facet = true;
        $facet3->is_facet = false;

        $facet1->save();
        $facet2->save();
        $facet3->save();

        $names = $this->facetTable->getActiveFacetNames();

        // Should contain the names of the active facets.
        $this->assertEquals(array('facet1', 'facet2'), $names);

    }


}
