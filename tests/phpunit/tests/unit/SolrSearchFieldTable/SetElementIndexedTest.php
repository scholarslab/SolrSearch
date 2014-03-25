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

        // Field not searchable.
        $field = $this->fieldTable->findByElement($element);
        $this->assertEquals(0, $field->is_indexed);

        $this->fieldTable->setElementIndexed('Dublin Core', 'Date');

        // Should make facet searchable.
        $field = $this->fieldTable->findByElement($element);
        $this->assertEquals(1, $field->is_indexed);

    }


}
