<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

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
     * `setElementIndexed` should flip on the indexing flag for the field that
     * corresponds to the passed element.
     */
    public function testSetElementIndexed()
    {

        $element = $this->elementTable->findByElementSetNameAndElementName(
            'Dublin Core', 'Date'
        );

        // Flip off indexing.
        $field = $this->fieldTable->findByElement($element);
        $field->is_indexed = 0;
        $field->save();

        // Flip on indexing.
        $this->fieldTable->setElementIndexed('Dublin Core', 'Date');

        // Should enable indexing.
        $field = $this->_reload($field);
        $this->assertEquals(1, $field->is_indexed);

    }


}
