<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


/**
 * This is a collection of utilities for working with queries, facets, etc.
 **/
class SolrSearch_Helpers_Query
{

    /**
     * This returns an array containing the Solr GET/POST parameters.
     *
     * @param array  $req        The request array to pull the parameters from.
     * This defaults to null, which then gets set to $_REQUEST.
     * @param string $qParam     The name of the q parameter. This defaults to
     * 'solrq'.
     * @param string $facetParam The name of the facet parameter. This defaults
     * to 'solrfacet'.
     * @param array  $other      A list of other parameters to pull and include
     * in the output.
     *
     * @return array This array is keyed on 'q' and 'facet'.
     */
    public static function getParams(
        $req=null, $qParam='solrq', $facetParam='solrfacet', $other=null
    ) {

        if (is_null($req)) $req = $_REQUEST;
        $params = array('q' => '*:*');

        if (isset($req[$qParam])) {
            $params['q'] = preg_replace('/:/', ' ', $req[$qParam]);
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
     * @param string  $facet   Facet link
     * @param string  $label   Facet label
     * @param integer $count   Facet count
     *
     * @return string
     */
    public static function createFacetHtml($current, $facet, $label, $count)
    {
        $html = '';
        $uri = SolrSearch_Helpers_View::getBaseUrl();

        $escaped = htmlspecialchars(
            SolrSearch_Helpers_Query::escapeFacet($label), ENT_QUOTES
        );

        // if the query contains one of the facets in the list
        if (isset($current['q'])
            && strpos($current['q'], "$facet:\"$label\"") !== false
        ) {
            //generate remove facet link
            $removeFacetLink = SolrSearch_Helpers_Query::removeFacet(
                $facet,
                $label
            );

            $html .= "<div class='fn'><b>$label</b></div> "
                . "<div class='fc'>$removeFacetLink</div>";
        } else {
            if (!empty($current['q'])) {
                $q = 'solrq=' . html_escape($current['q']) . '&';
            } else {
                $q = '';
            }
            if (!empty($current['facet'])) {
                $facetq = "{$current['facet']}+AND+$facet:&#x022;$escaped&#x022;";
            } else {
                $facetq = "$facet:&#x022;$escaped&#x022;";
            }

            $link = $uri . '?' . $q . 'solrfacet=' . $facetq;

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
        $uri = SolrSearch_Helpers_View::getBaseUrl();
        $queryParams = SolrSearch_Helpers_Query::getParams();
        $html = '';

        // If there is only one tokenized string in the query and that string is
        // *:*, return ALL TERMS text.

        if (SolrSearch_Helpers_Query::isNullQuery($queryParams)) {
            $html .= '<span class="appliedFilter constraint query">';
            $html .= '<span class="filterValue">' . __('ALL TERMS') . '</span>';
            $html .= '</span>';

        } else {
            // Otherwise, continue with process of displaying facets and removal
            // links.

            if (!empty($queryParams['q']) && $queryParams['q'] !== '*:*') {
                $facet = array_key_exists('facet', $queryParams) ? $queryParams['facet'] : '';
                $html .= '<span class="appliedFilter constraint query">';
                $html .= '<span class="filterValue">' . $queryParams['q'] . '</span>';
                $html .= "<a class='btnRemove imgReplace' alt='remove' href='$uri?solrfacet=$facet'>";
                $html .= __('Remove constraint %s', $queryParams['q']);
                $html .= '</a>';
                $html .= '</span>';
            }

            if (!empty($queryParams['facet'])) {
                foreach (explode(' AND ', $queryParams['facet']) as $param) {
                    $paramSplit = explode(':', $param);
                    $facet = $paramSplit[0];
                    $label = substr($paramSplit[1], 1, -1);

                    if (strpos($param, '_') !== false) {
                        $category = SolrSearch_Helpers_View::lookupElement($facet);
                    } else {
                        $category = ucwords($facet);
                    }

                    if ($facet != '*') {
                        $link = SolrSearch_Helpers_Query::removeFacet($facet, $label);
                        $cleaned = str_replace('\\', '', $label);
                        $html .= "<span class='appliedFilter constraint filter filter-subject_topic_facet'>";
                        $html .= "<span class='filterName'>$category</span>";
                        $html .= "<span class='filterValue'>$cleaned</span> $link</span>";
                    }
                }
            }
        }

        return $html;
    }

    /**
     * This returns true if the search searches for nothing.
     *
     * @param array $params The GET parameters as an associative array.
     *
     * @return bool $is_null Is the query null?
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function isNullQuery($params)
    {
        $is_null = false;

        if (empty($params)) {
            $is_null = true;

        } else {
            $nullq     = false;
            $nullfacet = false;

            $nullq = (!isset($params['q']) || $params['q'] === '*:*' || $params['q'] === '');
            $nullfacet = (!isset($params['facet']) || $params['facet'] === '');

            $is_null = $nullq && $nullfacet;
        }

        return $is_null;
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
        $queryParams = SolrSearch_Helpers_Query::getParams();
        $newParams = array();
        $query = array();

        if (isset($queryParams['q'])) {
            $query[] = "solrq={$queryParams['q']}";
        }

        if (isset($queryParams['facet'])) {
            $facetKey = "$facet:\"$label\"";
            $facetQuery = array();
            foreach (explode(' AND ', $queryParams['facet']) as $value) {
                if ($value !== $facetKey) {
                    $valueParts = explode(':', $value, 2);
                    $key        = $valueParts[0];
                    $cleaned    = SolrSearch_Helpers_Query::escapeFacet(
                        substr($valueParts[1], 1, -1)
                    );
                    $facetQuery[] = html_escape("$key:\"$cleaned\"");
                }
            }
            if (!empty($facetQuery)) {
                array_push(
                    $query,
                    'solrfacet=' . implode('+AND+', $facetQuery)
                );
            }
        }

        if (empty($query)) {
            array_push($query, html_escape('solrq=*:*'));
        }

        $removeFacetLink = '<a class="btnRemove imgReplace" href="'
            . '?' . implode('&', $query) . '" rel="tag">'
            . str_replace("\\", "", $label)
            . '</a>';
        return $removeFacetLink;
    }

    /**
     * This takes a keyed array of query parameters and returns an array with
     * the values to pass to Solr.
     *
     * @param array  $query   This is the array of parameters passed as GET or
     * POST parameters.
     * @param string $default This is the value of 'q' to use if one isn't
     * provided. This defaults to '*:*'.
     *
     * @return string The 'q' parameter to pass to the Solr engine.
     */
    public static function createQuery($query, $default='*:*')
    {
        $q = (isset($query['q']) && strlen($query['q']) > 0) ?
            $query['q'] :
            $default;

        if (isset($query['facet']) && strlen($query['facet']) > 0) {
            $q .= " AND ({$query['facet']})";
        }

        return $q;
    }

    /**
     * Looks up elements for the index
     *
     * @param string $field Element field to look up
     *
     * @return string $element Element name
     */
    public static function elementLookup($field)
    {
        $fieldArray = explode('_', $field);
        $fieldId = $fieldArray[0];
        $db = get_db();
        $element = $db->getTable('Element')->find($fieldId);
        return $element['name'];
    }

    /**
     * Parses facet field to determine human readable version.
     *
     * @param string $facetName Facet to parse.
     *
     * @return string $header Human readable facet name
     * @author Wayne Graham <wsg4w@virginia.edu>
     **/
    public static function parseFacet($facetName)
    {
        $db     = get_db();
        $table  = $db->getTable('SolrSearchFacet');
        $select = $table->getSelect();
        $select->where("{$table->getTableAlias()}.name=?", $facetName);
        $facet  = $table->fetchObject($select);
        return $facet->label;
    }

    /**
     * This Solr-escapes this facet value.
     *
     * The regex comes from
     * @link http://fragmentsofcode.wordpress.com/2010/03/10/escape-special-characters-for-solrlucene-query/
     *
     * @param string $facet The facet value to clean up.
     *
     * @return string $facet The escaped facet value.
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function escapeFacet($facet)
    {
        return preg_replace(
            '/(?<!\\\\)([&|+\-!(){}[\]^"~*?:])/',
            '\\\\$1',
            $facet
        );
    }

}
