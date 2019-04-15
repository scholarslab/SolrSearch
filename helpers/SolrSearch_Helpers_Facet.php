<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_Helpers_Facet
{

    private static $fieldMap;

    private static function loadFacetLabels()
    {
        if (!isset(self::$fieldMap)) {
            self::$fieldMap = array();
            foreach (get_db()
                         ->getTable('SolrSearchField')
                         ->findBy(array('is_facet' => true)) as $field) {
                $solrField = $field->element_id ? $field->element_id . '_s' : $field->slug;
                self::$fieldMap[$solrField] = $field->label;
            }
        }
    }

    /**
     * Fetch the facet key given the label
     *
     * @param string $label the facet label
     * @return string|null the facet key
     */
    public static function labelToKey($label)
    {
        if (!isset(self::$fieldMap)) {
            self::loadFacetLabels();
        }
        $map = array_flip(self::$fieldMap);
        return isset($map[$label]) ? $map[$label] : null;
    }

    /**
     * Fetch the facet label given the key
     *
     * @param string $label the facet key
     * @return string|null the facet label
     */
    public static function keyToLabel($key)
    {
        if (!isset(self::$fieldMap)) {
            self::loadFacetLabels();
        }
        return isset(self::$fieldMap[$key]) ? self::$fieldMap[$key] : $key;
    }

    /**
     * Parse an array of Label:Value facets.
     *
     * @param array $params an array of facet pairs
     *
     * @return array The parsed parameters.
     */
    public static function parseExternalFacets($params)
    {
        $facets = array();
        if (is_array($params)) {
            foreach ($params as $param) {
                preg_match('/(?P<field>[^:]+):(?P<value>.+)/',
                    $param, $matches
                );

                // Collapse into an array of pairs.
                if ($matches) {
                    $key = self::labelToKey($matches['field']);
                    if ($key) {
                        $facets[] = array($key, $matches['value']);
                    }
                }
            }
        }

        return $facets;
    }

    /**
     * Convert the raw Solr facet param into an array.
     *
     * @param string $param the raw Solr facet string
     *
     * @return array The parsed parameters.
     */
    public static function parseRawFacets($param)
    {

        $facets = array();

        if (is_string($param)) {

            // Extract the field/value facet pairs.
            preg_match_all('/(?P<field>[\w]+):"(?P<value>[^"]+)"/',
                $param, $matches
            );

            // Collapse into an array of pairs.
            foreach ($matches['field'] as $i => $field) {
                $facets[] = array($field, $matches['value'][$i]);
            }

        }

        return $facets;
    }

    /**
     * Convert the $_GET facet & f[] parameters in a parsed array.
     *
     * @return array The parsed parameters.
     */
    public static function parseFacets()
    {
        return array_merge(
            self::parseRawFacets(@$_GET['facet']),
            self::parseExternalFacets(@$_GET['f'])
        );
    }

    /**
     * Parse Solr filter (fq) values from incoming
     * facet parameters.
     *
     * @return array an array of fq filter values
     */
    public static function parseFilters()
    {
        $fq = array_map(function ($pair) {
            return "{$pair[0]}:\"{$pair[1]}\"";
        }, self::parseFacets());
        if (get_option('solr_search_items_only')) {
            $fq[] = "resulttype:Item";
        }
        return $fq;
    }

    /**
     * Rebuild the URL with a new array of facets.
     *
     * @param array $facets The parsed facets.
     * @return string The new URL.
     */
    public static function makeRawUrl($facets)
    {

        // Collapse the facets to `:` delimited pairs.
        $fParam = array();
        foreach ($facets as $facet) {
            $fParam[] = "{$facet[0]}:\"{$facet[1]}\"";
        }

        // Implode on ` AND `.
        $fParam = urlencode(implode(' AND ', $fParam));

        // Get the `q` parameter, reverting to ''.
        $qParam = array_key_exists('q', $_GET) ? $_GET['q'] : '';

        // Get the base results URL.
        $results = url('search');
        // String together the final route.
        return htmlspecialchars("$results?q=$qParam&facet=$fParam");

    }

    /**
     * Rebuild the URL with a new array of facets.
     *
     * @param array $facets The parsed facets.
     * @return string The new URL.
     */
    public static function makeUrl($facets)
    {

        // Collapse the facets to `:` delimited pairs.
        $fParam = array();
        foreach ($facets as $facet) {
            $label = self::keyToLabel($facet[0]);
            if ($label) {
                $fParam[] = "f[]=" . urlencode($label) . ':' . urlencode($facet[1]);
            }
        }

        // Implode on ` AND `.
        $fParam = implode('&', $fParam);


        // Get the `q` parameter, reverting to ''.
        $qParam = array_key_exists('q', $_GET) ? "q=" . $_GET['q'] : '';

        // Get the base results URL.
        $results = url('search');

        // Join the params
        $params = implode('&', array_filter(array($qParam, $fParam)));

        // Join the full URL
        $full = implode('?', array_filter(array($results, $params)));

        // Return the final route.
        return htmlspecialchars($full);
    }


    /**
     * Add a facet to the current URL.
     *
     * @param string $field The facet field.
     * @param string $value The facet value.
     * @return string The new URL.
     */
    public static function addFacet($field, $value)
    {

        // Get the current facets.
        $facets = self::parseFacets();

        // Add the facet, if it's not already present.
        if (!in_array(array($field, $value), $facets)) {
            $facets[] = array($field, $value);
        }

        // Rebuild the route.
        return self::makeUrl($facets);

    }


    /**
     * Remove a facet to the current URL.
     *
     * @param string $field The facet field.
     * @param string $value The facet value.
     * @return string The new URL.
     */
    public static function removeFacet($field, $value)
    {

        // Get the current facets.
        $facets = self::parseFacets();

        // Reject the field/value pair.
        $reduced = array();
        foreach ($facets as $facet) {
            if ($facet !== array($field, $value)) $reduced[] = $facet;
        }

        // Rebuild the route.
        return self::makeUrl($reduced);

    }
}
