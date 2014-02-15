<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class SolrSearchPluginTest_ExhibitPages extends SolrSearch_Test_AppTestCase
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
     * When a page is added to a public exhibit, it should be indexed in Solr.
     */
    public function testIndexNewPageInPublicExhibit()
    {
        // TODO
    }


    /**
     * When an existing exhibit is switched from private to public, a page in
     * the exhibit should be indexed in Solr.
     */
    public function testIndexPageWhenExhibitSetPublic()
    {
        // TODO
    }


    /**
     * When a page is added to a private exhibit, it should not be indexed.
     */
    public function testDontIndexNewPageInPrivateExhibit()
    {
        // TODO
    }


    /**
     * When an existing exhibit is switched from public to private, a page in
     * the exhibit should be removed from Solr.
     */
    public function testRemovePageWhenExhibitSetPrivate()
    {
        // TODO
    }


    /**
     * When a public exhibit is deleted, a page in the exhibit should be
     * removed from Solr.
     */
    public function testRemovePageWhenExhibitDeleted()
    {
        // TODO
    }


}
