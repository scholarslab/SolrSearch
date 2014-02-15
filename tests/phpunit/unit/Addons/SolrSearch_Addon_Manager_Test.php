<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_Addon_Manager_Test extends SolrSearch_Test_AppTestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->_installPluginOrSkip('ExhibitBuilder');
        $this->_installPluginOrSkip('SimplePages');
        $this->_loadExhibits();
    }

    public function testAddonDir()
    {
        $mgr = new SolrSearch_Addon_Manager($this->db);
        $this->assertEquals(
            realpath(SOLR_DIR . '/addons'),
            realpath($mgr->addonDir)
        );
    }

    public function testParseAll()
    {
        $mgr = new SolrSearch_Addon_Manager($this->db);
        $mgr->parseAll();
        $this->assertCount(3, $mgr->addons);
    }

    public function testFindAddonForRecordExhibit()
    {
        $mgr   = new SolrSearch_Addon_Manager($this->db);
        $table = $this->db->getTable('Exhibit');
        $rows  = $table->fetchObjects($table->getSelect());

        $mgr->parseAll();

        $this->assertNotEmpty($rows);
        $addon = $mgr->findAddonForRecord($rows[0]);
        $this->assertNotNull($addon);
        $this->assertEquals('exhibits', $addon->name);
    }

    public function testFindAddonForRecordExhibitPage()
    {
        $mgr   = new SolrSearch_Addon_Manager($this->db);
        $table = $this->db->getTable('ExhibitPage');
        $rows  = $table->fetchObjects($table->getSelect());

        $mgr->parseAll();

        $this->assertNotEmpty($rows);
        $addon = $mgr->findAddonForRecord($rows[0]);
        $this->assertNotNull($addon);
        $this->assertEquals('exhibit_pages', $addon->name);
    }

    public function testReindexAddons()
    {
        $mgr  = new SolrSearch_Addon_Manager($this->db);
        $docs = $mgr->reindexAddons();

        $this->assertCount(3, $docs);
        $this->assertInstanceOf('Apache_Solr_Document', $docs[0]);
    }

    private function _testSolrDoc($record, $doc, $public)
    {
        if ($public) {
            $this->assertNotNull($doc);
            $this->assertInstanceOf('Apache_Solr_Document', $doc);

            $resulttype = $doc->getField('resulttype');
            $resulttype = $resulttype['value'][0];

            switch ($resulttype) {
            case 'Exhibits':
                $titleField = 'exhibits_title_s';
                break;
            case 'Exhibit Pages':
                $titleField = 'exhibit_pages_title_s';
                break;
            }

            $title = $doc->getField($titleField);
            $this->assertContains($record->title, $title['value']);

        } else {
            $this->assertNull($doc);
        }
    }

    public function testIndexRecord()
    {
        $mgr     = new SolrSearch_Addon_Manager($this->db);
        $extable = $this->db->getTable('Exhibit');

        foreach ($extable->findAll() as $ex) {
            $doc = $mgr->indexRecord($ex);
            $this->_testSolrDoc($ex, $doc, $ex->public);

            foreach ($ex->getPages() as $page) {
                $doc = $mgr->indexRecord($page);
                $this->_testSolrDoc($page, $doc, $ex->public);
            }
        }
    }

}
