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

    $escaped = htmlspecialchars($label, ENT_QUOTES);

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
    $uri = SolrSearch_ViewHelpers::getBaseUrl();
    $queryParams = SolrSearch_QueryHelpers::getParams();
    $html = '';

    // If there is only one tokenized string in the query and that string is
    // *:*, return ALL TERMS text.

    if (empty($queryParams)
      || (isset($queryParams['q']) && $queryParams['q'] == '*:*'
      && !isset($queryParams['facet']))
    ) {
        $html .= '<span class="appliedFilter constraint query">';
        $html .= '<span class="filterValue">ALL TERMS</span>';
        $html .= '</span>';

    } else {
      // Otherwise, continue with process of displaying facets and removal
      // links.

      if (isset($queryParams['q']) && $queryParams['q'] !== '*:*') {
        $html .= '<span class="appliedFilter constraint query">';
        $html .= '<span class="filterValue">' . $queryParams['q'] . '</span>';
        $html .= "<a class='btnRemove imgReplace' alt='remove' href='$uri?solrfacet={$queryParams['facet']}'>";
        $html .= 'Remove constraint ' . $queryParams['q'];
        $html .= '</a>';
        $html .= '</span>';
      }

      if (isset($queryParams['facet'])) {
        foreach (explode(' AND ', $queryParams['facet']) as $param) {
          $paramSplit = explode(':', $param);
          $facet = $paramSplit[0];
          $label = substr($paramSplit[1], 1, -1);

          if (strpos($param, '_') !== false) {
            $category = SolrSearch_ViewHelpers::lookupElement($facet);
          } else {
            $category = ucwords($facet);
          }

          if ($facet != '*') {
            $link = SolrSearch_QueryHelpers::removeFacet($facet, $label);
            $html .= "<span class='appliedFilter constraint filter filter-subject_topic_facet'>";
            $html .= "<span class='filterName'>$category</span>";
            $html .= "<span class='filterValue'>$label</span> $link</span>";
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
    $removeFacetLink = "<a href='$uri?";
    $query = array();

    if (isset($queryParams['q'])) {
      $query[] = "solrq={$queryParams['q']}";
    }

    if (isset($queryParams['facet'])) {
      $facetKey = "$facet:\"$label\"";
      $facetQuery = array();
      foreach (explode(' AND ', $queryParams['facet']) as $value) {
        if ($value !== $facetKey) {
          $facetQuery[] = html_escape($value);
        }
      }
      if (!empty($facetQuery)) {
        array_push($query, 'solrfacet=' . implode('+AND+', $facetQuery));
      }
    }

    if (empty($query)) {
      array_push($query, html_escape('solrq=*:*'));
    }
    
    $removeFacetLink = '<a class="btnRemove imgReplace" href="' . $uri . '?' . implode('&', $query) . '" rel="tag">' . $label . '</a>';
    //$removeFacetLink = "[<a href='$uri?" . implode('&', $query) . '\'>X</a>]';
    return $removeFacetLink;
  }

  /**
   * This takes a keyed array of query parameters and returns an array with the
   * values to pass to Solr.
   *
   * @param array  $query   This is the array of parameters passed as GET or POST
   * parameters.
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
   * Parses facet field to determine human readable version.
   *
   * @param string $facet Facet to parse.
   *
   * @return string $header Human readable facet name
   * @author Wayne Graham <wsg4w@virginia.edu>
   **/
  public static function parseFacet($facet)
  {
    $header = '';
    if (strstr($facet, ' ')) {
      $header = SolrSearch_QueryHelpers::createFacetHtml($facet);
    } else {
      $header = ucwords($facet);
    }

    return $header;
  }

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
