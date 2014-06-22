<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTest_GetElement extends SolrSearch_Case_Default
{


    /**
     * `getElement` should return the parent element.
     */
    public function testParentElement()
    {

        $title = $this->elementTable->findByElementSetNameAndElementName(
            'Dublin Core', 'Title'
        );

        $facet = new SolrSearchField($title);

        // Should return the parent element.
        $this->assertEquals($title->id, $facet->getElement()->id);

    }


    /**
     * `getElement` should return NULL when no element is defined.
     */
    public function testNoParentElement()
    {

        $facet = new SolrSearchField();

        // Should return NULL.
        $this->assertNull($facet->getElementSet());

    }


}
