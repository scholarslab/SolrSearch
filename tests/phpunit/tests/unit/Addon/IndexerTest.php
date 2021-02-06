<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchAddonTest_Indexer extends SolrSearch_Case_Default
{

    public function setUpLegacy()
    {

        parent::setUpLegacy();
        $this->_installPluginOrSkip('ExhibitBuilder');
        $this->_installPluginOrSkip('SimplePages');

        $this->mgr = new SolrSearch_Addon_Manager($this->db);
        $addons = $this->mgr->parseAll();
        $this->exhibits = $addons['exhibits'];
        $this->exhibitPages = $addons['exhibit_pages'];

        $this->idxr = new SolrSearch_Addon_Indexer($this->db);

        $ex = $this->_exhibit();
        $ex->addTags(["test", "exhibit", "tagged"]);
        $ex->save();
        $this->_exhibitPage($ex);

        $this->_simplePage();
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

        $this->assertGreaterThan(0, count($docs));
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
        $docs = $this->idxr->indexAllAddon($this->exhibits);

        $this->assertCount(1, $docs);

        $title = $docs[0]->getField('exhibits_title_t');
        $this->assertEquals('Test Title', $title['value'][0]);

        $descr = $docs[0]->getField('exhibits_description_t');
        $this->assertEquals('Test description', $descr['value'][0]);
    }

    public function testIndexPage()
    {
        $docs = $this->idxr->indexAllAddon($this->exhibitPages);

        $this->assertCount(1, $docs);
        $title = $docs[0]->getField('exhibit_pages_title_t');

        $this->assertEquals('Test Title', $title['value'][0]);
    }

    public function testIndexPrivateExhibit()
    {
        $this->_exhibit($public = false, $slug = "test-private", $title = "Private");
        $docs = $this->idxr->indexAllAddon($this->exhibits);

        $this->assertCount(1, $docs);
        $title = $docs[0]->getField('exhibits_title_t');
        $this->assertEquals('Test Title', $title['value'][0]);
    }

    public function testIndexTagged()
    {
        $docs = $this->idxr->indexAllAddon($this->exhibits);

        $this->assertCount(1, $docs);
        $tags = $docs[0]->getField('tag');

        $this->assertContains('test',    $tags['value']);
        $this->assertContains('exhibit', $tags['value']);
        $this->assertContains('tagged',  $tags['value']);
    }

    public function testIndexExhibitResultType()
    {
        $docs = $this->idxr->indexAllAddon($this->exhibits);

        $this->assertCount(1, $docs);
        $resultType = $docs[0]->getField('resulttype');

        $this->assertNotEmpty($resultType);
        $this->assertContains('Exhibit', $resultType['value']);
    }

    public function testIndexPageResultType()
    {
        $docs = $this->idxr->indexAllAddon($this->exhibitPages);

        $this->assertCount(1, $docs);
        $resultType = $docs[0]->getField('resulttype');

        $this->assertTrue($resultType !== false);
        $this->assertContains('Exhibit Page', $resultType['value']);
    }

}
