<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTableTest_SetElementIndexed
    extends SolrSearch_Case_Default
{


    /**
     * When `true` is passed as the value, `setElementIndexed` should flip on
     * the faceting flag for the field that corresponds to the element.
     */
    public function testFlipOn()
    {

        $element = $this->elementTable->findByElementSetNameAndElementName(
            'Dublin Core', 'Date'
        );

        // Flip off indexing.
        $field = $this->fieldTable->findByElement($element);
        $field->is_indexed = 0;
        $field->save();

        // Flip on indexing.
        $this->fieldTable->setElementIndexed('Dublin Core', 'Date', true);

        // Should enable indexing.
        $field = $this->_reload($field);
        $this->assertEquals(1, $field->is_indexed);

    }


    /**
     * When `false` is passed as the value, `setElementIndexed` should flip
     * off the faceting flag for the field that corresponds to the element.
     */
    public function testFlipOff()
    {

        $element = $this->elementTable->findByElementSetNameAndElementName(
            'Dublin Core', 'Date'
        );

        // Flip on indexing.
        $field = $this->fieldTable->findByElement($element);
        $field->is_indexed = 1;
        $field->save();

        // Flip off indexing.
        $this->fieldTable->setElementIndexed('Dublin Core', 'Date', false);

        // Should enable indexing.
        $field = $this->_reload($field);
        $this->assertEquals(0, $field->is_indexed);

    }


}
