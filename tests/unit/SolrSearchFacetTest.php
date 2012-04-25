<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Facet row tests.
 *
 * @package     omeka
 * @subpackage  SolrSearch
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class SolrSearch_SolrSearchFacetTest extends SolrSearch_Test_AppTestCase
{

    /**
     * Install the plugin.
     *
     * @return void.
     */
    public function setUp()
    {
        $this->setUpPlugin();
    }

    /**
     * Test get and set on columns.
     *
     * @return void.
     */
    public function testAttributeAccess()
    {

        // Create a record.
        $facet = new SolrSearchFacet();

        // Set.
        $facet->element_id = 1;
        $facet->element_set_id = 2;
        $facet->name = 'name';
        $facet->is_facet = 1;
        $facet->is_displayed = 1;
        $facet->is_sortable = 1;
        $facet->save();

        // Re-get the facet object.
        $facet = $this->facetsTable->find($facet->id);

        // Get.
        $this->assertEquals($facet->element_id, 1);
        $this->assertEquals($facet->element_set_id, 2);
        $this->assertEquals($facet->name, 'name');
        $this->assertEquals($facet->is_facet, 1);
        $this->assertEquals($facet->is_displayed, 1);
        $this->assertEquals($facet->is_sortable, 1);

    }

}
