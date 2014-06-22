<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTest_FacetKey extends SolrSearch_Case_Default
{


    /**
     * `facetKey` should return the `_s`-suffixed slug when the field is
     * associated with a metadata element.
     */
    public function testElementField()
    {

        // Get the Dublin Core "Title" field.
        $title = $this->elementTable->findByElementSetNameAndElementName(
            'Dublin Core', 'Title'
        );

        $field = new SolrSearchField($title);

        // Should add the `_s` suffix.
        $this->assertEquals("{$title->id}_s", $field->facetKey());

    }


    /**
     * `facetKey` should just return the slug when the field is not linked to
     * a metadata element.
     */
    public function testCategoryField()
    {

        $field = $this->_field('slug');

        // Should just return the slug.
        $this->assertEquals('slug', $field->facetKey());

    }


}
