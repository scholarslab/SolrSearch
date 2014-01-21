<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_SolrSearchFacetTest extends SolrSearch_Test_AppTestCase
{

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
        $facet->label = 'facet';
        $facet->name = 'name';
        $facet->is_facet = 1;
        $facet->is_displayed = 1;
        $facet->save();

        // Re-get the facet object.
        $facet = $this->facetsTable->find($facet->id);

        // Get.
        $this->assertEquals($facet->element_id, 1);
        $this->assertEquals($facet->element_set_id, 2);
        $this->assertEquals($facet->name, 'name');
        $this->assertEquals($facet->is_facet, 1);
        $this->assertEquals($facet->is_displayed, 1);

    }

    /**
     * getElementSet() should return the name of the parent element set
     * when the element_set_id is non-null.
     *
     * @return void.
     */
    public function testGetElementSetWhenKeyExists()
    {

        // Get element and element set.
        $element = $this->elementTable->find(1);
        $elementSet = $this->elementSetTable->find(1);

        // Create a facet.
        $facet = new SolrSearchFacet;
        $facet->element_id = $element->id;
        $facet->element_set_id = $elementSet->id;
        $facet->label = 'facet';
        $facet->name = 'facet';
        $facet->save();

        // Get element set name.
        $this->assertEquals(
            $facet->getElementSet()->id, $elementSet->id
        );

    }

    /**
     * getElementSet() should return null when the element_set_id is null.
     *
     * @return void.
     */
    public function testGetElementSetWhenKeyDoesNotExist()
    {

        // Create a facet.
        $facet = new SolrSearchFacet;
        $facet->label = 'facet';
        $facet->name = 'facet';
        $facet->save();

        // Get element set name.
        $this->assertNull($facet->getElementSet());

    }

}
