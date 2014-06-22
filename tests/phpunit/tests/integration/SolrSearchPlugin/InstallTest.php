<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class SolrSearchPluginTest_Install extends SolrSearch_Case_Default
{


    /**
     * The `install` hook should add facet mappings for the generic Omeka
     * categories that are unaffiliated with items.
     */
    public function testAddOmekaCategoriesFacets()
    {

        $facets = array(
            $this->fieldTable->findBySlug('tag'),
            $this->fieldTable->findBySlug('collection'),
            $this->fieldTable->findBySlug('itemtype'),
            $this->fieldTable->findBySlug('resulttype')
        );

        foreach ($facets as $facet) {

            // Facet should exist.
            $this->assertNotNull($facet);

            // Should make viewable / searchable.
            $this->assertEquals(1, $facet->is_indexed);
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
            $facet = $this->fieldTable->findByElement($element);

            // Facet should exist.
            $this->assertNotNull($facet);

            // Should not be used as a facet.
            $this->assertEquals(0, $facet->is_facet);

            // DC "Title" and "Description" should be searchable.
            if (in_array($element->name, array('Title', 'Description'))) {
                $this->assertEquals(1, $facet->is_indexed);
            }

            // But other elements should not be searchable.
            else $this->assertEquals(0, $facet->is_indexed);

        }

    }


}
