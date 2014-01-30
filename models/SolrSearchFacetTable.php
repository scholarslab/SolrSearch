<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFacetTable extends Omeka_Db_Table
{


    /**
     * Get all facets grouped by element set id.
     *
     * @return array $facets The ElementSet-grouped facets.
     */
    public function groupByElementSet()
    {

        $groups = array();

        foreach ($this->findAll() as $facet) {

            // Get element set name.
            $set = $facet->getElementSetName();

            // Add the facet to its element set group (or create it).
            if (array_key_exists($set, $groups)) $groups[$set][] = $facet;
            else $groups[$set] = array($facet);

        }

        return $groups;

    }


}
