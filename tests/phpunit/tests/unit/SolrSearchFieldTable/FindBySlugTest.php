<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

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

        $facet = new SolrSearchField();
        $facet->label = 'facet';
        $facet->slug  = 'facet';
        $facet->save();

        $retrieved = $this->facetTable->findBySlug('facet');
        $this->assertEquals($facet->id, $retrieved->id);

    }


}
