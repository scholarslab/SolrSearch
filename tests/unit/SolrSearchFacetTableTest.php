<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Facet table tests.
 *
 * @package     omeka
 * @subpackage  SolrSearch
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class SolrSearch_SolrSearchFacetTableTest extends SolrSearch_Test_AppTestCase
{

    /**
     * Install the plugin.
     *
     * @return void.
     */
    public function setUp()
    {

        // Install plugin.
        $this->setUpPlugin();

        // Empty facets table.
        foreach ($this->facetsTable->findAll() as $facet) {
            $facet->delete();
        }

    }

    /**
     * groupByElementSet() should return the facets grouped by element set.
     *
     * @return void.
     */
    public function testGroupByElementSet()
    {

        // Get element and element set.
        $element = $this->elementTable->find(1);
        $elementSet = $this->elementSetTable->find(1);

        // Create facet without element_set_id.
        $noElementSetFacet = new SolrSearchFacet;
        $noElementSetFacet->name = 'No Element Set';
        $noElementSetFacet->save();

        // Create facet with element_set_id.
        $elementSetFacet = new SolrSearchFacet;
        $elementSetFacet->element_id;
        $elementSetFacet->element_set_id;

        // Group facets.
        $groups = $this->facetsTable->groupByElementSet();
        print_r($groups);

    }

}
