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
        $this->setUpExhibitBuilder();
        $this->loadModels();

        $this->mgr = new SolrSearch_Addon_Manager($this->db);
        $addons = $this->mgr->parseAll();
        $this->exhibits = $addons['exhibits'];
    }

    private function setUpExhibitBuilder()
    {
        $broker = get_plugin_broker();
        $this->_addHooksAndFilters($broker, 'ExhibitBuilder');

        $helper = new Omeka_Test_Helper_Plugin();
        $helper->setUp('ExhibitBuilder');
    }

    private function loadModels()
    {
    }

    public function testExhibitBuilderInstalled()
    {
        $table = $this->db->getTable('Exhibits');
        $this->assertNotNull($table);

        $tables = $this->db->fetchAssoc('SHOW TABLES;');
        // print_r($tables);
        $this->assertArrayHasKey('omeka_exhibits', $tables);
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

    public function testIndexExhibit()
    {
        $this->assertTrue(false, 'testIndexExhibit');
    }

    public function testIndexSection()
    {
        $this->assertTrue(false, 'testIndexSection');
    }

    public function testIndexPage()
    {
        $this->assertTrue(false, 'testIndexPage');
    }

    public function testIndexPrivateExhibit()
    {
        $this->assertTrue(false, 'testIndexPrivateExhibit');
    }

    public function testIndexPrivateSection()
    {
        $this->assertTrue(false, 'testIndexPrivateSection');
    }

    public function testIndexPrivatePage()
    {
        $this->assertTrue(false, 'testIndexPrivatePage');
    }

    public function testIndexTagged()
    {
        $this->assertTrue(false, 'testIndexTagged');
    }

    public function testIndexExhibitResultType()
    {
        $this->assertTrue(false, 'testIndexExhibitResultType');
    }

    public function testIndexSectionResultType()
    {
        $this->assertTrue(false, 'testIndexSectionResultType');
    }

    public function testIndexPageResultType()
    {
        $this->assertTrue(false, 'testIndexPageResultType');
    }

    public function testAfterSaveRecord()
    {
        $this->assertTrue(false, 'testAfterSaveRecord');
    }

    public function testAfterDeleteRecord()
    {
        $this->assertTrue(false, 'testAfterDeleteRecord');
    }

}

