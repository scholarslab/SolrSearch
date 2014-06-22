<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTest_GetElementSet extends SolrSearch_Case_Default
{


    /**
     * `getElementSet` should return the parent element set.
     */
    public function testParentElement()
    {

        $dublinCore = $this->elementSetTable->findByName(
            'Dublin Core'
        );

        $title = $this->elementTable->findByElementSetNameAndElementName(
            'Dublin Core', 'Title'
        );

        $facet = new SolrSearchField($title);

        // Should return the parent element set.
        $this->assertEquals($dublinCore->id, $facet->getElementSet()->id);

    }


    /**
     * `getElementSet` should return NULL when no element is defined.
     */
    public function testNoParentElement()
    {

        $facet = new SolrSearchField();

        // Should return NULL.
        $this->assertNull($facet->getElementSet());

    }


}
