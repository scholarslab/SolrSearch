<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTableTest_SetElementFaceted
    extends SolrSearch_Case_Default
{


    /**
     * When `true` is passed as the value, `setElementFaceted` should flip on
     * the faceting flag for the field that corresponds to the element.
     */
    public function testFlipOn()
    {

        $element = $this->elementTable->findByElementSetNameAndElementName(
            'Dublin Core', 'Date'
        );

        // Flip off faceting.
        $field = $this->fieldTable->findByElement($element);
        $field->is_facet = 0;
        $field->save();

        // Flip on faceting.
        $this->fieldTable->setElementFaceted('Dublin Core', 'Date', true);

        // Should enable faceting.
        $field = $this->_reload($field);
        $this->assertEquals(1, $field->is_facet);

    }


    /**
     * When `false` is passed as the value, `setElementFaceted` should flip
     * off the faceting flag for the field that corresponds to the element.
     */
    public function testFlipOff()
    {

        $element = $this->elementTable->findByElementSetNameAndElementName(
            'Dublin Core', 'Date'
        );

        // Flip on faceting.
        $field = $this->fieldTable->findByElement($element);
        $field->is_facet = 1;
        $field->save();

        // Flip off faceting.
        $this->fieldTable->setElementFaceted('Dublin Core', 'Date', false);

        // Should enable faceting.
        $field = $this->_reload($field);
        $this->assertEquals(0, $field->is_facet);

    }


}
