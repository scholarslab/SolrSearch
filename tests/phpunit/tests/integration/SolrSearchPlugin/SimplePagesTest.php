<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class SolrSearchPluginTest_SimplePages extends SolrSearch_Case_Default
{


    /**
     * Install Simple Pages or skip the suite.
     */
    public function setUp()
    {
        parent::setUp();
        $this->_installPluginOrSkip('SimplePages');
    }


    /**
     * When a new page is added, it should be indexed in Solr.
     */
    public function testIndexNewPublicPage()
    {

        // Add a public page.
        $page = $this->_simplePage(true);

        // Should add a Solr document.
        $this->_assertRecordInSolr($page);

    }


    /**
     * When an existing page is switched from private to public, it should be
     * indexed in Solr.
     */
    public function testIndexWhenPageSetPublic()
    {

        // Add a private page.
        $page = $this->_simplePage(false);

        // Set the page public.
        $page->is_published = true;
        $page->save();

        // Should add a Solr document.
        $this->_assertRecordInSolr($page);

    }


    /**
     * When a new private page is added, it should not be indexed in Solr.
     */
    public function testDontIndexNewPrivatePage()
    {

        // Add a private page.
        $page = $this->_simplePage(false);

        // Should not add a Solr document.
        $this->_assertNotRecordInSolr($page);

    }


    /**
     * When an existing page is switched from public to private, it should be
     * removed from Solr.
     */
    public function testRemovePageWhenSetPrivate()
    {

        // Add a public page.
        $page = $this->_simplePage(true);

        // Set the page private.
        $page->is_published = false;
        $page->save();

        // Should remove Solr document.
        $this->_assertNotRecordInSolr($page);

    }


    /**
     * When a public page is deleted, it should be removed from Solr.
     */
    public function testRemovePageWhenDeleted()
    {

        // Add a public page.
        $page = $this->_simplePage(true);

        // Delete.
        $page->delete();

        // Should remove Solr document.
        $this->_assertNotRecordInSolr($page);

    }


    /**
     * The result type should be indexed.
     */
    public function testIndexResultType()
    {

        // Add a page to the index.
        $page = $this->_simplePage(true);

        // Get the Solr document for the page.
        $document = $this->_getRecordDocument($page);

        // Should index the result type.
        $this->assertEquals('Simple Page', $document->resulttype);

    }


    /**
     * The page title should be indexed.
     */
    public function testIndexTitle()
    {

        // Add a page called "title".
        $page = $this->_simplePage(true, 'title');

        // Get the Solr document for the page.
        $document = $this->_getRecordDocument($page);

        // Should index title.
        $this->assertEquals('title', $document->title);

    }


    /**
     * The page text should be indexed.
     */
    public function testIndexText()
    {

        // Add a page with text.
        $page = $this->_simplePage(true);
        $page->text = 'text';
        $page->save();

        // Get the Solr document for the page.
        $document = $this->_getRecordDocument($page);

        // Get the name of the `text` Solr key.
        $indexKey = $this->_getAddonKey($page, 'text');

        // Should index the text field.
        $this->assertEquals('text', $document->$indexKey);

    }


}
