<?php
/**
 * SolrSearch Omeka Plugin helpers.
 *
 * Default helpers for the SolrSearch plugin
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package    omeka
 * @subpackage SolrSearch
 * @author     "Scholars Lab"
 * @copyright  2010 The Board and Visitors of the University of Virginia
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @version    $Id$
 * @link       http://www.scholarslab.org
 *
 * PHP version 5
 *
 */
?><?php

/**
 * This is a collection of utilities for working with queries, facets, etc.
 **/
class SolrSearch_QueryHelpers
{
    /**
     * This returns an array containing the Solr GET/POST parameters.
     *
     * @param array  $require    The request array to pull the parameters from.
     * This defaults to null, which then gets set to $_REQUEST.
     * @param string $qParam     The name of the q parameter. This defaults to
     * 'solrq'.
     * @param string $facetParam The name of the facet parameter. This defaults to
     * 'solrfacet'.
     * @param array $other       A list of other parameters to pull and include in
     * the output.
     *
     * @return array This array is keyed on 'q' and 'facet'.
     */
    public static function getParams(
        $req=null, $qParam='solrq', $facetParam='solrfacet', $other=null
    ) {
        if ($req === null) {
            $req = $_REQUEST;
        }
        $params = array();

        if (isset($req[$qParam])) {
            $params['q'] = $req[$qParam];
        }

        if (isset($req[$facetParam])) {
            $params['facet'] = $req[$facetParam];
        }

        if ($other !== null) {
            foreach ($other as $key) {
                if (array_key_exists($key, $req)) {
                    $params[$key] = $req[$key];
                }
            }
        }

        return $params;
    }

}

