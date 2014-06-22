<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTableTest_GetActiveFacetKeys
    extends SolrSearch_Case_Default
{


    /**
     * Delete any facet mappings registered when the plugin is installed.
     */
    public function setUp()
    {
        parent::setUp();
        $this->_clearFieldMappings();
    }


    /**
     * `getActiveFacetKeys` should return a list containing the facet keys of
     * all fields that are activated as facets.
     */
    public function testGetActiveFacetKeys()
    {

        $field1 = $this->_field('field1');
        $field2 = $this->_field('field2');
        $field3 = $this->_field('field3');

        $field1->is_facet = true;
        $field2->is_facet = true;
        $field3->is_facet = false;

        $field1->save();
        $field2->save();
        $field3->save();

        $names = $this->fieldTable->getActiveFacetKeys();

        // Should contain the names of the active facets.
        $this->assertEquals(array('field1', 'field2'), $names);

    }


}
