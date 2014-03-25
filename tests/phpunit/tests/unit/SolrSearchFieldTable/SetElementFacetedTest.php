<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

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
     * `setElementFaceted` should flip on the faceting flag for the field that
     * corresponds to the passed element.
     */
    public function testSetElementIndexed()
    {

        $element = $this->elementTable->findByElementSetNameAndElementName(
            'Dublin Core', 'Date'
        );

        // Field not faceted.
        $field = $this->fieldTable->findByElement($element);
        $this->assertEquals(0, $field->is_facet);

        $this->fieldTable->setElementFaceted('Dublin Core', 'Date');

        // Should make field faceted.
        $field = $this->fieldTable->findByElement($element);
        $this->assertEquals(1, $field->is_facet);

    }


}
