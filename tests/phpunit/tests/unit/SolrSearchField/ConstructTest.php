<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTest_Construct extends SolrSearch_Case_Default
{


    /**
     * If a parent element is passed, `__construct` set the element reference
     * and the slug/label fields.
     */
    public function testElementPassed()
    {

        $title = $this->elementTable->findByElementSetNameAndElementName(
            'Dublin Core', 'Title'
        );

        $facet = new SolrSearchField($title);

        // Should set `element_id`, `slug`, and `label`.
        $this->assertEquals($title->id, $facet->element_id);
        $this->assertEquals($title->id, $facet->slug);
        $this->assertEquals('Title', $facet->label);

    }


    /**
     * The element-derived fields should be empty if no element is passed.
     */
    public function testNoElementPassed()
    {

        $facet = new SolrSearchField();

        // Should not set `element_id`, `slug`, or `label`.
        $this->assertNull($facet->element_id);
        $this->assertNull($facet->slug);
        $this->assertNull($facet->label);

    }


}
