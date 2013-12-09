<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class Table_SolrSearchFacet extends Omeka_Db_Table
{


    /**
     * Get all facets grouped by element set id.
     *
     * @return array $facets The ElementSet-grouped facets.
     */
    public function groupByElementSet()
    {

        // Get all facets.
        $facets = $this->findAll();
        $facetGroups = array();

        // Group by element set.
        foreach ($facets as $facet) {

            // Get element set name.
            $setName = !is_null($facet->element_set_id) ?
                $facet->getElementSet()->name : __('Omeka Categories');

            // If the key exists, push.
            if (array_key_exists($setName, $facetGroups)) {
                array_push($facetGroups[$setName], $facet);
            } else {
                // If not, create and push.
                $facetGroups[$setName] = array($facet);
            }

        }

        return $facetGroups;

    }


}
