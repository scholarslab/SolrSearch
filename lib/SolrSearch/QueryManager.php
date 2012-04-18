<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 *
 * PHP version 5
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package     omeka
 * @subpackage  fedoraconnector
 * @author      Scholars' Lab <>
 * @author      Ethan Gruber <ewg4x@virginia.edu>
 * @author      Adam Soroka <ajs6f@virginia.edu>
 * @author      Wayne Graham <wayne.graham@virginia.edu>
 * @author      Eric Rochester <err8n@virginia.edu>
 * @copyright   2010 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 * @version     $Id$
 * @link        http://omeka.org/add-ons/plugins/SolrSearch/
 */


/**
 * This class takes the GET/PUT parameters and reads the Solr search parameters 
 * from it. It also makes it easy to modify those parameters and generate a URL 
 * for that.
 *
 * WARNING: This is deprecated. Use the static functions in 
 * SolrSearch_QueryHelper instead. If there is functionality missing there, it 
 * needs to be added. This is not a fall-back. It won't be updated or developed 
 * further.
 */
class SolrSearch_QueryManager
{

    protected $_keyword;
    protected $_facets;
    protected $_baseUrl;
    protected $_extra;

    /**
     * This constructs a new QueryManager.
     *
     * @param array  $query   This is the query from the GET/PUT parameters.
     * It defaults to $_REQUEST.
     * @param string $baseUrl This is the results URI. It defaults to 
     * '/solr-search/results/'.
     * @param array  $extra   This is the extra parameters to add to the URL 
     * to use when creating URLs.
     */
    public function __construct($query=null, $baseUrl=null, $extra=null) {
        if ($query === null) {
            $query = $_REQUEST;
        }
        if ($baseUrl === null) {
            $baseUrl = uri('/solr-search/results/');
        }
        if ($extra === null) {
            $extra = array();
        }

        if (array_key_exists('solrq', $query)) {
            $this->_keyword = $query['solrq'];
        } else {
            $this->_keyword = '';
        }
        if (array_key_exists('solrfacet', $query)) {
            $this->_facets = $this->_parseFacets($query['solrfacet']);
        } else {
            $this->_facets = array();
        }

        $this->_baseUrl = $baseUrl;
        $this->_extra = $extra;
    }

    /**
     * This tests whether the current query is empty or not.
     *
     * @return boolean True if the query is only searching for *:*.
     */
    public function isEmpty() {
        return (($this->_keyword == '*:*' || strlen($this->_keyword) == 0) &&
                empty($this->_facets));
    }

    /**
     * This parses the facets input parameter.
     *
     * @param string $facets The facets input parameter.
     *
     * @return array The parsed facets.
     */
    protected function _parseFacets($facets) {
        $parsed = array();

        foreach (explode(' AND ', $facets) as $facet) {
            if (strlen($facet) > 0) {
                $parts = explode(':', $facet);
                if (count($parts) == 2) {
                    $parsed[$parts[0]] = $parts[1];
                }
            }
        }

        asort($parsed);
        return $parsed;
    }

    /**
     * This returns the keyword query from the input data.
     *
     * @return string The input keyword query.
     */
    public function getQuery() {
        return $this->_keyword;
    }

    /**
     * This returns the parsed array of facets data.
     *
     * @return array The facets from the input query.
     */
    public function getFacets() {
        return $this->_facets;
    }

    /**
     * This tests whether the facet is defined by the current query.
     *
     * @param string $facet The facet field.
     *
     * @return boolean True if the facet is defined for the query.
     */
    public function hasFacet($facet) {
        return array_key_exists($facet, $this->_facets);
    }

    /**
     * This returns the value for a single facet field.
     *
     * @param string $facet The facet field.
     *
     * @return string|null The value of the facet in the input parameters.
     */
    public function getFacetParameter($facet) {
        if (array_key_exists($facet, $this->_facets)) {
            return $this->_facets[$facet];
        } else {
            return null;
        }
    }

    /**
     * This returns the base URL for creating queries.
     *
     * @return string The base URL.
     */
    public function getBaseUrl() {
        return $this->_baseUrl;
    }

    /**
     * This creates the link for the current search parameters.
     *
     * @param array $extra Extra parameters to add to the URL (on top of those 
     * passed into the constructor). Optional.
     *
     * @return string The URL for this query.
     */
    public function makeLink($extra=null) {
        return $this->_makeLink(
            $this->getQuery(),
            $this->getFacets(),
            ($extra === null) ? array() : $extra
        );
    }

    /**
     * This takes the raw parameters and constructs a link.
     *
     * @param string $q
     * @param array  $facets
     * @param array  $extras
     *
     * @return string The URL.
     */
    private function _makeLink($q, $facets, $extras) {
        $url = $this->getBaseUrl();
        $params = array();

        if (strlen($q) > 0) {
            array_push($params, 'solrq=' . $this->_escape($q));
        }

        $facetParams = array();
        foreach ($facets as $name => $value) {
            if (strlen($value) > 0) {
                array_push($facetParams, $name . ':' . $this->_escape($value));
            }
        }
        if (count($facetParams) > 0) {
            array_push($params, 'solrfacet=' . implode('+AND+', $facetParams));
        }

        foreach ($this->_extra as $key => $value) {
            array_push($params, $key . '=' . $this->_escape($value));
        }

        foreach ($extras as $key => $value) {
            array_push($params, $key . '=' . $this->_escape($value));
        }

        if (count($params) > 0) {
            $url .= '?' . implode('&', $params);
        }

        return $url;
    }

    private function _escape($value) {
        return str_replace(':', '%3A', urlencode($value));
    }

    /**
     * This creates the link for the current search parameters with a different 
     * keyword query.
     *
     * @param string $keyword The keyword parameter to use in the new query.
     * @param array  $extra   Extra parameters to add to the URL (on top of 
     * those passed into the constructor). Optional.
     *
     * @return string THe modified URL for the query.
     */
    public function makeLinkAddQuery($keyword, $extra=null) {
        return $this->_makeLink(
            $keyword,
            $this->getFacets(),
            ($extra === null) ? array() : $extra
        );
    }

    /**
     * This creates the link for the current search parameters without the 
     * keyword part.
     *
     * @param array  $extra   Extra parameters to add to the URL (on top of 
     * those passed into the constructor). Optional.
     *
     * @return string THe modified URL for the query.
     */
    public function makeLinkRemoveQuery($extra=null) {
        return $this->_makeLink(
            '',
            $this->getFacets(),
            ($extra === null) ? array() : $extra
        );
    }

    /**
     * This creates the link for the current seach parameters with a facet 
     * added.
     *
     * @param string $name    The name of the facet to add to the parameters.
     * @param string $value   The value of the facet.
     * @param array  $extra   Extra parameters to add to the URL (on top of 
     * those passed into the constructor). Optional.
     *
     * @return string THe modified URL for the query.
     */
    public function makeLinkAddFacet($name, $value, $extra=null) {
        $facets = $this->_facets;
        $facets[$name] = $value;
        return $this->_makeLink(
            $this->getQuery(),
            $facets,
            ($extra === null) ? array() : $extra
        );
    }

    /**
     * This creates the link for the current seach parameters with a facet 
     * removed.
     *
     * @param string $name    The name of the facet to remove.
     * @param array  $extra   Extra parameters to add to the URL (on top of 
     * those passed into the constructor). Optional.
     *
     * @return string THe modified URL for the query.
     */
    public function makeLinkRemoveFacet($name, $extra=null) {
        $facets = $this->_facets;
        $facets[$name] = '';
        return $this->_makeLink(
            $this->getQuery(),
            $facets,
            ($extra === null) ? array() : $extra
        );
    }

}


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
