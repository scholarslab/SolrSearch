<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class SolrSearchPluginTest_Elements extends SolrSearch_Case_Default
{


    /**
     * When a new element is created, a facet mapping should be added;
     * @group elements
     */
    public function testAddFacetMappingWhenElementAdded()
    {

        // Add new element.
        $element = $this->_element();

        // Should create a new facet mapping for the element.
        $this->assertNotNull($this->fieldTable->findByElement($element));

    }


    /**
     * When an element is deleted, its facet mapping should be deleted.
     * @group elements
     */
    public function testRemoveFacetMappingWhenElementDeleted()
    {

        // Add new element.
        $element = $this->_element();

        // Get the element's facet.
        $facet = $this->fieldTable->findByElement($element);

        // Delete.
        $element->delete();

        // Should remove the facet for the element.
        $this->assertNull($this->fieldTable->find($facet->id));

    }


}
