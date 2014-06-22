<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTableTest_FindByText extends SolrSearch_Case_Default
{


    /**
     * Delete any facet mappings registered when the plugin is installed.
     */
    public function setUp()
    {
        parent::setUp();
        $this->_clearFieldMappings();
    }


    /**
     * `findByText` should return the facet linked to a given text.
     */
    public function testFindByText()
    {

        $title = $this->elementTable->findByElementSetNameAndElementName(
            'Dublin Core', 'Title'
        );

        // Create a field for "Title".
        $field = new SolrSearchField($title);
        $field->save();

        // Get a "Title" element text.
        $texts = $this->_item()->getElementTexts('Dublin Core', 'Title');

        $retrieved = $this->fieldTable->findByText($texts[0]);
        $this->assertEquals($field->id, $retrieved->id);

    }


}
