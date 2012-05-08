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

class SolrSearch_Addon_Indexer_Test extends SolrSearch_Test_AppTestCase
{
    
    public function setUp()
    {
        $this->setUpPlugin();

        $this->mgr = new SolrSearch_Addon_Manager($this->db);
        $addons = $this->mgr->parseAll();
        $this->exhibits = $addons['exhibits'];
    }

    public function testMakeSolrName()
    {
        $indexer = new SolrSearch_Addon_Indexer();
        $this->assertEquals(
            'exhibits_title_s',
            $indexer->makeSolrName($this->exhibits, 'title')
        );
        $this->assertEquals(
            'exhibits_description_s',
            $indexer->makeSolrName($this->exhibits, 'description')
        );
        $this->assertEquals(
            'section_pages_title_s',
            $indexer->makeSolrName($this->mgr->addons['section_pages'], 'title')
        );
    }

    public function testIndexAddons()
    {
        $this->assertTrue(false, 'testIndexAddons');
    }

}

