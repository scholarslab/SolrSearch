<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * @package     omeka
 * @subpackage  SolrSearch
 * @author      Scholars' Lab <>
 * @author      Eric Rochester <erochest@virginia.edu>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class SolrSearch_Addon_Manager_Test extends SolrSearch_Test_AppTestCase
{

    public function setUp()
    {
        $this->setUpPlugin();
    }

    public function testAddonDir()
    {
        $mgr = new SolrSearch_Addon_Manager($this->db);
        $this->assertEquals(
            realpath(SOLR_SEARCH_PLUGIN_DIR . '/addons'),
            realpath($mgr->addonDir)
        );
    }

    public function testParseAll()
    {
        $mgr = new SolrSearch_Addon_Manager($this->db);
        $mgr->parseAll();
        $this->assertCount(4, $mgr->addons);
    }

}

