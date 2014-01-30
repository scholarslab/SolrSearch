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
     * `getElementSet` should return the name of the parent element set.
     */
    public function testGetElementSetWhenKeyExists()
    {

        // Get element and element set.
        $element = $this->elementTable->find(1);
        $elementSet = $this->elementSetTable->find(1);

        // Create a facet.
        $facet = new SolrSearchFacet;
        $facet->label           = 'facet';
        $facet->name            = 'facet';
        $facet->element_set_id  = $elementSet->id;
        $facet->element_id      = $element->id;
        $facet->save();

        // Get element set name.
        $this->assertEquals(
            $facet->getElementSet()->id, $elementSet->id
        );

    }

    /**
     * `getElementSet` should return NULL when no element set is defined.
     */
    public function testGetElementSetWhenKeyDoesNotExist()
    {

        // Create a facet.
        $facet = new SolrSearchFacet;
        $facet->label   = 'facet';
        $facet->name    = 'facet';
        $facet->save();

        // Should return NULL.
        $this->assertNull($facet->getElementSet());

    }

}
