<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFacetTest_GetOriginalLabel extends SolrSearch_Case_Default
{


    /**
     * `getOriginalLabel` should return capitalized, public-facing labels for
     * the generic Omeka category facets.
     */
    public function testGenericCategoryFacets()
    {

        $facet = new SolrSearchFacet();
        $facet->name = 'tag';
        $this->assertEquals('Tag', $facet->getOriginalLabel());

        $facet = new SolrSearchFacet();
        $facet->name = 'collection';
        $this->assertEquals('Collection', $facet->getOriginalLabel());

        $facet = new SolrSearchFacet();
        $facet->name = 'itemtype';
        $this->assertEquals('Item Type', $facet->getOriginalLabel());

        $facet = new SolrSearchFacet();
        $facet->name = 'resulttype';
        $this->assertEquals('Result Type', $facet->getOriginalLabel());

    }


    /**
     * For facets that are associated with elements, `getOriginalLabel` should
     * return the name of the parent element.
     */
    public function testElementFacets()
    {

        $title = $this->elementTable->findByElementSetNameAndElementName(
            'Dublin Core', 'Title'
        );

        $facet = new SolrSearchFacet($title);

        // Should return the parent element.
        $this->assertEquals('Title', $facet->getOriginalLabel());

    }


}
