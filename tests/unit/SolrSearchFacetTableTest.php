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

if (!defined('SOLR_SEARCH_PLUGIN_DIR')) {
    define('SOLR_SEARCH_PLUGIN_DIR', dirname(__FILE__) . '/../..');
}
require_once APP_DIR . '/models/Plugin.php';
require_once SOLR_SEARCH_PLUGIN_DIR . '/SolrSearchPlugin.php';
require_once SOLR_SEARCH_PLUGIN_DIR . '/tests/SolrSearch_Test_AppTestCase.php';

class SolrSearch_SolrSearchFacetTableTest extends SolrSearch_Test_AppTestCase
{

    /**
     * Install the plugin.
     *
     * @return void.
     */
    public function setUp()
    {

        // Install plugin.
        $this->setUpPlugin();

        // Empty facets table.
        foreach ($this->facetsTable->findAll() as $facet) {
            $facet->delete();
        }

    }

    /**
     * groupByElementSet() should return the facets grouped by element set.
     *
     * @return void.
     */
    public function testGroupByElementSet()
    {

        // Get element and element set.
        $element = $this->elementTable->find(1);
        $elementSet = $this->elementSetTable->find(1);

        // Create facet without element_set_id.
        $noElementSetFacet = new SolrSearchFacet;
        $noElementSetFacet->name = 'No Element Set';
        $noElementSetFacet->label = 'No Element Set';
        $noElementSetFacet->save();

        // Create facets with element_set_id.
        $elementSetFacet1 = new SolrSearchFacet;
        $elementSetFacet1->name = 'Element Set 1';
        $elementSetFacet1->label = 'Element Set 1';
        $elementSetFacet1->element_id = $element->id;
        $elementSetFacet1->element_set_id = $elementSet->id;
        $elementSetFacet1->save();
        $elementSetFacet2 = new SolrSearchFacet;
        $elementSetFacet2->name = 'Element Set 2';
        $elementSetFacet2->label = 'Element Set 2';
        $elementSetFacet2->element_id = $element->id;;
        $elementSetFacet2->element_set_id = $elementSet->id;
        $elementSetFacet2->save();

        // Group facets and check formation.
        $groups = $this->facetsTable->groupByElementSet();

        $this->assertEquals(
            $groups[$elementSet->name][1]->id,
            $elementSetFacet1->id
        );

        $this->assertEquals(
            $groups[$elementSet->name][0]->id,
            $elementSetFacet2->id
        );

        $this->assertEquals(
            $groups['Omeka Categories'][0]->id,
            $noElementSetFacet->id
        );

    }

}
