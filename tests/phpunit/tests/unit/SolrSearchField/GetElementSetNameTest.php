<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTest_GetElementSetName extends SolrSearch_Case_Default
{


    /**
     * `getElementSetName` should return the parent element set name.
     */
    public function testParentElement()
    {

        $title = $this->elementTable->findByElementSetNameAndElementName(
            'Dublin Core', 'Title'
        );

        $facet = new SolrSearchField($title);

        // Should return the parent element set name.
        $this->assertEquals('Dublin Core', $facet->getElementSetName());

    }


    /**
     * When no element exists, use the "Omeka Categories" catch-all.
     */
    public function testNoParentElement()
    {

        $facet = new SolrSearchField();

        // Should return the generic string.
        $this->assertEquals('Omeka Categories', $facet->getElementSetName());

    }


}
