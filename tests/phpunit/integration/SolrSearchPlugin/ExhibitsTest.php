<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class SolrSearchPluginTest_Exhibits extends SolrSearch_Test_AppTestCase
{


    /**
     * Install Exhibit Builder or skip the suite.
     */
    public function setUp()
    {
        parent::setUp();
        $this->_installPluginOrSkip('ExhibitBuilder');
    }


    /**
     * When a new exhibit is added, it should be indexed in Solr.
     */
    public function testIndexNewPublicExhibit()
    {
        // TODO
    }


    /**
     * When an existing exhibit is switched from private to public, it should
     * be indexed in Solr.
     */
    public function testIndexWhenExhibitSetPublic()
    {
        // TODO
    }


    /**
     * When a new private exhibit is added, it should not be indexed in Solr.
     */
    public function testDontIndexNewPrivateExhibit()
    {
        // TODO
    }


    /**
     * When an existing exhibit is switched from public to private, it should
     * be removed from Solr.
     */
    public function testRemoveExhibitWhenSetPrivate()
    {
        // TODO
    }


    /**
     * When a public exhibit is deleted, it should be removed from Solr.
     */
    public function testRemoveExhibitWhenDeleted()
    {
        // TODO
    }


    /**
     * The page URL should be indexed.
     */
    public function testIndexUrl()
    {

        // Add a page to the index.
        $page = $this->_page(true);

        // Get the Solr document for the page.
        $document = $this->_getRecordDocument($page);

        // Should index the URL.
        $this->assertEquals(record_url($page, 'show'), $document->url);

    }


    /**
     * The result type should be indexed.
     */
    public function testIndexResultType()
    {

        // Add a page to the index.
        $page = $this->_page(true);

        // Get the Solr document for the page.
        $document = $this->_getRecordDocument($page);

        // Should index the result type.
        $this->assertEquals('Simple Pages', $document->resulttype);

    }


    /**
     * The page title should be indexed.
     */
    public function testIndexTitle()
    {

        // Add a page called "title".
        $page = $this->_page(true, 'title');

        // Get the Solr document for the page.
        $document = $this->_getRecordDocument($page);

        // Should index title.
        $this->assertEquals('title', $document->title);

    }


    /**
     * The page text should be indexed.
     * @group pages
     */
    public function testIndexText()
    {

        // Add a page with text.
        $page = $this->_page(true);
        $page->text = 'text';
        $page->save();

        // Get the Solr document for the page.
        $document = $this->_getRecordDocument($page);

        // Get the name of the `text` Solr key.
        $textKey = $this->_getAddonSolrKey($page, 'text');

        // Should index the text field.
        $this->assertEquals('text', $document->$textKey);

    }


}
