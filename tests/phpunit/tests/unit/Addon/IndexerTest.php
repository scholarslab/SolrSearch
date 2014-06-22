<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchAddonTest_Indexer extends SolrSearch_Case_Default
{

    public function setUp()
    {

        parent::setUp();
        $this->_installPluginOrSkip('ExhibitBuilder');
        $this->_installPluginOrSkip('SimplePages');

        $this->mgr = new SolrSearch_Addon_Manager($this->db);
        $addons = $this->mgr->parseAll();
        $this->exhibits = $addons['exhibits'];

        $this->idxr = new SolrSearch_Addon_Indexer($this->db);
    }

    public function testMakeSolrName()
    {
        $indexer = $this->idxr;
        $this->assertEquals(
            'exhibits_title_t',
            $indexer->makeSolrName($this->exhibits, 'title')
        );
        $this->assertEquals(
            'exhibits_description_t',
            $indexer->makeSolrName($this->exhibits, 'description')
        );
        $this->assertEquals(
            'exhibit_pages_title_t',
            $indexer->makeSolrName($this->mgr->addons['exhibit_pages'], 'title')
        );
    }

    public function testIndexFields()
    {
        $idxr = $this->idxr;
        $docs = $idxr->indexAll($this->mgr->addons);

        // All addons have a title field, so check for that on every document.
        foreach ($docs as $doc) {
            $ok = false;
            foreach ($doc->getFieldNames() as $name) {
                $ok = $ok || stripos($name, '_title_') !== FALSE;
            }
            $this->assertTrue($ok, $doc->id);
        }
    }

    public function testIndexExhibit()
    {
        $docs = $this->idxr->indexAll($this->mgr->addons);

        foreach ($docs as $doc) {
            if (($title = $doc->getField('exhibits_title_t')) !== false) {
                $descr = $doc->getField('exhibits_description_t');

                $this->assertEquals('Test Exhibit', $title['value'][0]);
                $this->assertEquals('Like Alice in Wonderland', $descr['value'][0]);
            }
        }
    }

    public function testIndexSection()
    {
        $docs = $this->idxr->indexAll($this->mgr->addons);

        foreach ($docs as $doc) {
            if (($title = $doc->getField('sections_title_t')) !== false) {
                $descr = $doc->getField('sections_descriptions_t');

                $this->assertContains(
                    $title['value'][0],
                    array("White Rabbit's House", "Mad Hatter's Tea Party")
                );
                $this->assertFalse($descr, print_r($descr, true));
            }
        }
    }

    public function testIndexPage()
    {
        $docs = $this->idxr->indexAll($this->mgr->addons);

        foreach ($docs as $doc) {
            if (($title = $doc->getField('section_pages_title_t')) !== false) {
                $this->assertContains(
                    $title['value'][0],
                    array("White Rabbit", "Dormouse")
                );
            }
        }
    }

    public function testIndexPageEntry()
    {
        $docs = $this->idxr->indexAll($this->mgr->addons);

        foreach ($docs as $doc) {
            if (
                ($title = $doc->getField('section_pages_title_t')) !== false
                && $title['value'][0] === 'Dormouse'
            ) {
                $text = $doc->getField('section_pages_text_t');
                $this->assertTrue($text !== false, $title['value'][0]);
                $this->assertCount(2, $text['value']);
                $this->assertContains(
                    $text['value'][0],
                    array('Yawwwn', 'Traveling far and wide')
                );
                $this->assertContains(
                    $text['value'][1],
                    array('Yawwwn', 'Traveling far and wide')
                );
            }
        }
    }

    public function testIndexPrivateExhibit()
    {
        $docs = $this->idxr->indexAll($this->mgr->addons);

        foreach ($docs as $doc) {
            if (($title = $doc->getField('exhibits_title_t')) !== false) {
                $this->assertNotEquals('Private Exhibit', $title['value'][0]);
            }
        }
    }

    public function testIndexPrivateSection()
    {
        $docs = $this->idxr->indexAll($this->mgr->addons);

        foreach ($docs as $doc) {
            if (($title = $doc->getField('sections_title_t')) !== false) {
                $this->assertNotEquals($title['value'][0], "Mud");
            }
        }
    }

    public function testIndexPrivatePage()
    {
        $docs = $this->idxr->indexAll($this->mgr->addons);

        foreach ($docs as $doc) {
            if (($title = $doc->getField('section_pages_title_t')) !== false) {
                $this->assertNotEquals($title['value'][0], "Earthworms");
                $this->assertNotEquals($title['value'][0], "Centipedes");
            }
        }
    }

    public function testIndexTagged()
    {
        $docs = $this->idxr->indexAll($this->mgr->addons);

        foreach ($docs as $doc) {
            if (($doc->getField('exhibits_title_t')) !== false) {
                $tags = $doc->getField('tag');

                $this->assertContains('test',    $tags['value']);
                $this->assertContains('exhibit', $tags['value']);
                $this->assertContains('tagged',  $tags['value']);
            }
        }
    }

    public function testIndexExhibitResultType()
    {
        $docs = $this->idxr->indexAll($this->mgr->addons);

        foreach ($docs as $doc) {
            if (($doc->getField('exhibits_title_t')) !== false) {
                $resultType = $doc->getField('resulttype');

                $this->assertTrue($resultType !== false);
                $this->assertContains('Exhibits', $resultType['value']);
            }
        }
    }

    public function testIndexSectionResultType()
    {
        $docs = $this->idxr->indexAll($this->mgr->addons);

        foreach ($docs as $doc) {
            if (($doc->getField('sections_title_t')) !== false) {
                $resultType = $doc->getField('resulttype');

                $this->assertTrue($resultType !== false);
                $this->assertContains('Sections', $resultType['value']);
            }
        }
    }

    public function testIndexPageResultType()
    {
        $docs = $this->idxr->indexAll($this->mgr->addons);

        foreach ($docs as $doc) {
            if (($doc->getField('section_pages_title_t')) !== false) {
                $resultType = $doc->getField('resulttype');

                $this->assertTrue($resultType !== false);
                $this->assertContains('Exhibit Pages', $resultType['value']);
            }
        }
    }

}
