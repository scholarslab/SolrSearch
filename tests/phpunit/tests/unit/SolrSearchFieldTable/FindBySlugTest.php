<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTableTest_FindBySlug extends SolrSearch_Case_Default
{


    /**
     * `findBySlug` should return the facet with a given slug.
     */
    public function testFindBySlug()
    {

        $field = new SolrSearchField();
        $field->label = 'field';
        $field->slug  = 'field';
        $field->save();

        $retrieved = $this->fieldTable->findBySlug('field');
        $this->assertEquals($field->id, $retrieved->id);

    }


}
