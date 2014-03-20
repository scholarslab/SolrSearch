<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTableTest_FindByName extends SolrSearch_Case_Default
{


    /**
     * `findByName` should return the facet with a given name.
     */
    public function testFindByName()
    {

        $facet = new SolrSearchField();
        $facet->label = 'facet';
        $facet->name  = 'facet';
        $facet->save();

        $retrieved = $this->facetTable->findByName('facet');
        $this->assertEquals($facet->id, $retrieved->id);

    }


}
