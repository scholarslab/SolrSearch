<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Table class for facets.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package omeka
 * @subpackage SolrSearch
 * @author Scholars' Lab
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @copyright 2010 The Board and Visitors of the University of Virginia
 * @link https://github.com/scholarslab/SolrSearch/
 *
 * PHP version 5
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

        // Get all facets.
        $facets = $this->findAll();
        $facetGroups = array();

        // Group by element set.
        foreach ($facets as $facet) {

            // Get element set name.
            $elementSet = $facet->getElementSet();

            // If the key exists, push.
            if (array_key_exists($elementSet, $facetGroups)) {
                array_push($facetGroups[$elementSet], $facet);
            }

            // If not, create and push.
            else {
                $facetGroups[$elementSet] = $facet;
            }

        }

        return $facetGroups;

    }


}
