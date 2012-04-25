<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Facet table tests.
 *
 * @package     omeka
 * @subpackage  SolrSearch
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class SolrSearch_SolrSearchFacetTableTest extends SolrSearch_Test_AppTestCase
{

    /**
     * Install the plugin.
     *
     * @return void.
     */
    public function setUp()
    {
        $this->setUpPlugin();    }

    /**
     * groupByElementSet() should return the facets grouped by element set.
     *
     * @return void.
     */
    public function testGroupByElementSet()
    {

    }

}
