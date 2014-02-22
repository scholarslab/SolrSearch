<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class FacetHelpersTest_GetParams extends SolrSearch_Case_Default
{


    /**
     * When a `q` parameter is set onf $_GET, it should be passed through to
     * the parsed parameter array.
     */
    public function testPassThroughQueryParameter()
    {
        // TODO
    }


    /**
     * When a `q` parameter is _not_ set onf $_GET, `q` should be set on the
     * parsed array to an empty string.
     */
    public function testAddEmptyQueryParameter()
    {
        // TODO
    }


    /**
     * When a `facet` parameter is passed, the raw string should be broken
     * apart and set as an array of field => value pairs.
     */
    public function testParseFacetParameter()
    {
        // TODO
    }


    /**
     * When a `facet` parameter is _not_ set onf $_GET, `facet` should be set
     * on the parsed array to an empty array.
     */
    public function testAddEmptyFacetParameter()
    {
        // TODO
    }


}
