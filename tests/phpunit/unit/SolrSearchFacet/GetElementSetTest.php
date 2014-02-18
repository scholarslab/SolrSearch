<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFacetTest_GetElementSet extends SolrSearch_Case_Default
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

        $facet = new SolrSearchFacet($title);

        // Should return the parent element set.
        $this->assertEquals($dublinCore->id, $facet->getElementSet()->id);

    }


    /**
     * `getElementSet` should return NULL when no element is defined.
     */
    public function testNoParentElement()
    {

        $facet = new SolrSearchFacet();

        // Should return NULL.
        $this->assertNull($facet->getElementSet());

    }


}
