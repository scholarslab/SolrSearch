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
    public function testAddOmekaCategoriesFacetMappings()
    {

        $facets = array(
            $this->_getFacetByName('tag'),
            $this->_getFacetByName('collection'),
            $this->_getFacetByName('itemtype'),
            $this->_getFacetByName('resulttype')
        );

        foreach ($facets as $facet) {

            // Should create facets.
            $this->assertNotNull($facet);

            // Should make viewable / searchable.
            $this->assertEquals(1, $facet->is_displayed);
            $this->assertEquals(1, $facet->is_facet);

        }

    }


}
