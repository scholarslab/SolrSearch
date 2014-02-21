<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_Helpers_Facet
{


    /**
     * Convert $_GET into an array with exploded facets.
     *
     * @return array The parsed parameters.
     */
    public static function getParams()
    {

        $params = $_GET;

        // Ensure the `q` parameter.
        if (!array_key_exists('q', $params)) $params['q'] = '';

        // Ensure the `facet` parameter.
        if (!array_key_exists('facet', $params)) $params['facet'] = array();

        else {

            // Extract the field/value facet pairs.
            preg_match_all('/(?P<field>[\w]+):"(?P<value>[\w]+)"/',
                $params['facet'], $matches
            );

            // Collapse into an array of pairs.
            $facet = array();
            foreach ($matches['field'] as $i => $field) {
                $facet[] = array($field, $matches['value'][$i]);
            }

            $params['facet'] = $facet;

        }

        return $params;

    }


    /**
     * Convert a parameters array into a URL.
     *
     * @param array $params The parsed parameters.
     * @return string The URL.
     */
    public static function makeUrl($params)
    {

        // Collapse the facets to `:` delimited pairs.
        $facets = array();
        foreach ($params['facet'] as $facet) {
            $facets[] = "{$facet[0]}:\"{$facet[1]}\"";
        }

        // Implode on ` AND `.
        $facets = implode(' AND ', $facets);

        // Get the base results URL.
        $results = url('solr-search/results');

        // String together the final route.
        return htmlspecialchars("$results?q={$params['q']}&facet=$facets");

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

        // Get the parameters.
        $params = self::getParams();

        // Add the facet, if it's not already present.
        if (!in_array(array($field, $value), $params['facet'])) {
            $params['facet'][] = array($field, $value);
        }

        // Rebuild the route.
        return self::makeUrl($params);

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

        // Get the parameters.
        $params = self::getParams();

        // Reject the field/value pair.
        $facets = array();
        foreach ($params['facet'] as $facet) {
            if ($facet !== array($field, $value)) $facets[] = $facet;
        }

        $params['facet'] = $facets;

        // Rebuild the route.
        return self::makeUrl($params);

    }


}
