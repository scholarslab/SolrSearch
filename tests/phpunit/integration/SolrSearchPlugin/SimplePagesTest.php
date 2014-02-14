<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class SolrSearchPluginTest_SimplePages extends SolrSearch_Test_AppTestCase
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
        $page = $this->_page(true);

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
        $page = $this->_page(false);

        // Set the page private.
        $page->is_published = true;
        $page->save();

        // Should add a Solr document.
        $this->_assertRecordInSolr($page);

    }


    /**
     * When a new private page is added, it should not be indexed in Solr.
     * @group pages
     */
    public function testDontIndexNewPrivatePage()
    {

        // Add a private page.
        $page = $this->_page(false);

        // Should not add a Solr document.
        $this->_assertNotRecordInSolr($page);

    }


    /**
     * When an existing page is switched from public to private, it should be
     * removed from Solr.
     */
    public function testRemovePageWhenSetPrivate()
    {
        // TODO
    }


    /**
     * When a public page is deleted, it should be removed from Solr.
     */
    public function testRemovePageWhenDeleted()
    {
        // TODO
    }


}
