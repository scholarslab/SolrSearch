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

    /**
     * Create a SolrFacetLink
     *
     * @param array   $current The current facet search.
     * @param string  $facet
     * @param string  $label
     * @param integer $count
     * @return string
     */
    public static function createFacetHtml($current, $facet, $label, $count)
    {
        $html = '';
        $uri = SolrSearch_ViewHelpers::getBaseUrl();

        // if the query contains one of the facets in the list
        if (isset($current['q'])
            && strpos($current['q'], "$facet:\"$label\"") !== false
        ) {
            //generate remove facet link
            $removeFacetLink = SolrSearch_QueryHelpers::removeFacet($facet, $label);
            $html .= "<div class='fn'><b>$label</b></div> "
                . "<div class='fc'>$removeFacetLink</div>";
        } else {
            if (isset($current['q'])) {
                $q = 'solrq=' . html_escape($current['q']) . '&';
            } else {
                $q = '';
            }
            if (isset($current['facet'])) {
                $facetq = "{$current['facet']}+AND+$facet:&#x022;$label&#x022;";
            } else {
                $facetq = "$facet:&#x022;$label&#x022;";
            }

            //otherwise just display a link to a new query with the facet count
            $html .= "<div class='fn'>"
                . "<a href='$uri?{$q}solrfacet=$facetq'>$label</a>"
                . "</div>"
                . "<div class='fc'>$count</div>";
        }

        return $html;
    }

    /**
     * Create a new anchor with a field popped
     *
     * @return string
     */
    public static function removeFacets()
    {
        $uri = SolrSearch_ViewHelpers::getBaseUrl();
        $queryParams = SolrSearch_QueryHelpers::getParams();
        $html = '';

        // If there is only one tokenized string in the query and that string is
        // *:*, return ALL TERMS text.

        if (empty($queryParams)
            || (isset($queryParams['q']) && $queryParams['q'] == '*:*'
                && !isset($queryParams['facet']))
        ) {
            $html .= '<li><b>ALL TERMS</b></li>';

        } else {
            // Otherwise, continue with process of displaying facets and removal
            // links.

            if (isset($queryParams['q'])) {
                $html .= "<li><b>Keyword:</b> {$queryParams['q']} "
                    . "[<a href='$uri?solrfacet={$queryParams['facet']}'>X</a>]"
                    . "</li>";
            }

            if (isset($queryParams['facet'])) {
                foreach (explode(' AND ', $queryParams['facet']) as $param) {
                    $paramSplit = explode(':', $param);
                    $facet = $paramSplit[0];
                    $label = trim($paramSplit[1], '"');

                    if (strpos($param, '_') !== false) {
                        $category = SolrSearch_ViewHelpers::lookupElement($facet);
                    } else {
                        $category = ucwords($facet);
                    }

                    if ($facet != '*') {
                        $link = SolrSearch_QueryHelpers::removeFacet($facet, $label);
                        $html .= "<li><b>$category:</b> $label $link</li>";
                    }
                }
            }
        }

        return $html;
    }

    /**
     * Return the current search URL with only the given facet removed.
     *
     * @param string $facet The facet to remove.
     * @param string $label The facet label (value) to remove.
     *
     * @return string The current search URL without the given facet.
     */
    public static function removeFacet($facet, $label)
    {
        // Deconstruct current query and remove particular facet.
        $queryParams = SolrSearch_QueryHelpers::getParams();
        $newParams = array();
        $removeFacetLink = "[<a href='$uri?";
        $query = array();

        if (isset($queryParams['q'])) {
            array_push($query, "solrq={$queryParams['q']}");
        }

        $queryParams = explode(' AND ', $_REQUEST['q']);
        if (isset($queryParams['facet'])) {
            $facetKey = "$facet:\"$label\"";
            $facetQuery = array();
            foreach (explode(' AND ', $queryParams['facet']) as $value) {
                if ($value != $facetKey) {
                    array_push($facetQuery, $value);
                }
            }
            if (!empty($facetQuery)) {
                array_push($query, implode('+AND+', $facetQuery));
            }
        }

        if (empty($query)) {
            array_push($query, html_escape('solrq=*:*'));
        }

        $removeFacetLink = "[<a href='$uri?" . implode('&', $query) . '\'>X</a>]';
        return $removeFacetLink;
    }

}

