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

        $this->idxr = new SolrSearch_Addon_Indexer();
    }

    private function setUpExhibitBuilder()
    {
        $broker = get_plugin_broker();
        $this->_addHooksAndFilters($broker, 'ExhibitBuilder');

        $helper = new Omeka_Test_Helper_Plugin();
        $helper->setUp('ExhibitBuilder');
    }

    private function createExhibit($exhibit)
    {
        $e = new Exhibit();
        $e->title       = property_exists($exhibit, 'title') ? $exhibit->title : null;
        $e->description = property_exists($exhibit, 'description') ? $exhibit->description : null;
        $e->public      = property_exists($exhibit, 'public') ? $exhibit->public : true;
        $e->public      = $e->public ? 1 : 0;
        $e->save();

        $i = 1;
        foreach ($exhibit->sections as $section) {
            $s = new ExhibitSection();
            $s->title       = property_exists($section, 'title') ? $section->title : null;
            $s->description = property_exists($section, 'description') ? $section->description : null;
            $s->exhibit_id  = $e->id;
            $s->order       = $i;
            $s->save();

            $j = 0;
            foreach ($section->pages as $page) {
                $p = new ExhibitPage();
                $p->title      = property_exists($page, 'title') ? $page->title : null;
                $p->section_id = $s->id;
                $p->order      = $j;
                $p->save();

                $k = 0;
                foreach ($page->entries as $entry) {
                    $item = $this->__item(
                        property_exists($entry, 'title') ? $entry->title : null,
                        property_exists($entry, 'subject') ? $entry->subject : null
                    );

                    $pe = new ExhibitPageEntry();
                    $pe->item_id = $item->id;
                    $pe->page_id = $p->id;
                    $pe->order   = $k;
                    $pe->text    = property_exists($entry, 'text') ? $entry->text : null;
                    $pe->caption = property_exists($entry, 'caption') ? $entry->caption : null;
                    $pe->save();

                    $k++;
                }

                $j++;
            }

            $i++;
        }

        return $e;
    }

    private function loadModels()
    {
        $filename = SOLR_SEARCH_PLUGIN_DIR . '/tests/fixtures/exhibits.json';
        $exhibits = json_decode(file_get_contents($filename));

        foreach ($exhibits as $exhibit) {
            $this->createExhibit($exhibit);
        }
    }

    public function testExhibitBuilderInstalled()
    {
        $table = $this->db->getTable('Exhibit');
        $this->assertNotNull($table);

        $tables = $this->db->fetchAssoc('SHOW TABLES;');
        $this->assertArrayHasKey('omeka_exhibits', $tables);

        $rows = $table->findAll();
        $this->assertNotEmpty($rows);
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
        $idxr = new SolrSearch_Addon_Indexer();
        $docs = $idxr->indexAll($this->mgr->addons);

        $this->assertNotEmpty($docs);
        $this->assertInstanceOf('Apache_Solr_Document', $docs[0]);
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

