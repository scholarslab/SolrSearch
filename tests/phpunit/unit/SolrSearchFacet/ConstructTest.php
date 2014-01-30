<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFacetTest_Construct extends SolrSearch_Test_AppTestCase
{

    /**
     * `__construct` should set the parent element, if one is provided.
     */
    public function testElementPassed()
    {

        $title = $this->elementTable->findByElementSetNameAndElementName(
            'Dublin Core', 'Title'
        );

        $facet = new SolrSearchFacet($title);

        // Should set `element_id`.
        $this->assertEquals($title->id, $facet->element_id);

    }

    /**
     * The `element_id` field should be left blank if no element is passed.
     */
    public function testNoElementPassed()
    {
        // TODO
    }

}
