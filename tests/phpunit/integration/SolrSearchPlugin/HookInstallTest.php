<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class SolrSearchPluginTest_HookInstall extends SolrSearch_Test_AppTestCase
{


    /**
     * The `install` hook should add facet mappings for the generic Omeka
     * categories that are unaffiliated with items.
     */
    public function testAddOmekaCategoriesFacets()
    {

        $facets = array(
            $this->_getFacetByName('tag'),
            $this->_getFacetByName('collection'),
            $this->_getFacetByName('itemtype'),
            $this->_getFacetByName('resulttype')
        );

        foreach ($facets as $facet) {

            // Facet should exist.
            $this->assertNotNull($facet);

            // Should make viewable / searchable.
            $this->assertEquals(1, $facet->is_displayed);
            $this->assertEquals(1, $facet->is_facet);

        }

    }


    /**
     * The `install` hook should add facet mappings for each of the individual
     * elements, with "Title" and "Description" displayed by default.
     */
    public function testAddElementFacets()
    {

        foreach ($this->elementTable->findAll() as $element) {

            // Try to find a facet.
            $facet = $this->_getFacetByElement($element);

            // Facet should exist.
            $this->assertNotNull($facet);

            // Elements should not be used as facets.
            $this->assertEquals(0, $facet->is_facet);

            // DC "Title" and "Description" should be searchable.
            if (in_array($element->name, array('Title', 'Description'))) {
                $this->assertEquals(1, $facet->is_displayed);
            }

            // But other elements should not be searchable.
            else $this->assertEquals(0, $facet->is_displayed);

        }

    }


}
