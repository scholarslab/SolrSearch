<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTest_HasElement extends SolrSearch_Case_Default
{


    /**
     * `hasElement` should return true when a parent element is provided.
     */
    public function testParentElement()
    {

        $title = $this->elementTable->findByElementSetNameAndElementName(
            'Dublin Core', 'Title'
        );

        $facet = new SolrSearchField($title);

        // True when a parent element exists.
        $this->assertTrue($facet->hasElement());

    }


    /**
     * `getElement` should return NULL when no element is defined.
     */
    public function testNoParentElement()
    {

        $facet = new SolrSearchField();

        // False when no parent element exists.
        $this->assertFalse($facet->hasElement());

    }


}
