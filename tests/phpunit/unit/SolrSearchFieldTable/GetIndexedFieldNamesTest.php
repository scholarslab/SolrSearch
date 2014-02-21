<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTableTest_GetIndexedFieldNames
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
     * `getIndexedFieldNames` should return a list containing the names of all
     * the fields that have been set as indexed.
     */
    public function testGetIndexedFieldNames()
    {

        $facet1 = $this->_facet('facet1');
        $facet2 = $this->_facet('facet2');
        $facet3 = $this->_facet('facet3');

        $facet1->is_indexed = true;
        $facet2->is_indexed = true;
        $facet3->is_indexed = false;

        $facet1->save();
        $facet2->save();
        $facet3->save();

        $names = $this->facetTable->getIndexedFieldNames();

        // Should contain the names of the indexed fields.
        $this->assertEquals(array('facet1', 'facet2'), $names);

    }


}
