<?php

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
    public static function parseFacets()
    {

        $facets = array();

        if (array_key_exists('facet', $_GET)) {

            // Extract the field/value facet pairs.
            preg_match_all('/(?P<field>[\w]+):"(?P<value>[^"]+)"/',
                $_GET['facet'], $matches
            );

            // Collapse into an array of pairs.
            foreach ($matches['field'] as $i => $field) {
                $facets[] = array($field, htmlspecialchars(strip_tags($matches['value'][$i])));
            }

        }

        return $facets;

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
            $fParam[] = "{$facet[0]}:\"{$facet[1]}\"";
        }

        // Implode on ` AND `.
        $fParam = urlencode(implode(' AND ', $fParam));

        // Get the `q` parameter, reverting to ''.
        $qParam = array_key_exists('q', $_GET) ? $_GET['q'] : '';

        // Get the base results URL.
        $results = url('solr-search');

        // String together the final route.
        return htmlspecialchars("$results?q=$qParam&facet=$fParam");

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


    /**
     * Get the human-readable label for a facet key.
     *
     * @param string $key The facet key.
     * @return string The label.
     */
    public static function keyToLabel($key)
    {
        $fields = get_db()->getTable('SolrSearchField');
        return $fields->findBySlug(rtrim($key, '_s'))->label;
    }


}
