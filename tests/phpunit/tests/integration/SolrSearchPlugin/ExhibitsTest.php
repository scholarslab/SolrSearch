<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class SolrSearchPluginTest_Exhibits extends SolrSearch_Case_Default
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

        // Add a public exhibit.
        $exhibit = $this->_exhibit(true);

        // Should add a Solr document.
        $this->_assertRecordInSolr($exhibit);

    }


    /**
     * When an existing exhibit is switched from private to public, it should
     * be indexed in Solr.
     */
    public function testIndexWhenExhibitSetPublic()
    {

        // Add a private exhibit.
        $exhibit = $this->_exhibit(false);

        // Set public.
        $exhibit->public = true;
        $exhibit->save();

        // Should add a Solr document.
        $this->_assertRecordInSolr($exhibit);

    }


    /**
     * When a new private exhibit is added, it should not be indexed in Solr.
     */
    public function testDontIndexNewPrivateExhibit()
    {

        // Add a private exhibit.
        $exhibit = $this->_exhibit(false);

        // Should add a Solr document.
        $this->_assertNotRecordInSolr($exhibit);

    }


    /**
     * When an existing exhibit is switched from public to private, it should
     * be removed from Solr.
     */
    public function testRemoveExhibitWhenSetPrivate()
    {

        // Add a public exhibit.
        $exhibit = $this->_exhibit(true);

        // Set private.
        $exhibit->public = false;
        $exhibit->save();

        // Should remove Solr document.
        $this->_assertNotRecordInSolr($exhibit);

    }


    /**
     * When a public exhibit is deleted, it should be removed from Solr.
     */
    public function testRemoveExhibitWhenDeleted()
    {

        // Add a public exhibit.
        $exhibit = $this->_exhibit(true);

        // Delete.
        $exhibit->delete();

        // Should remove Solr document.
        $this->_assertNotRecordInSolr($exhibit);

    }


    /**
     * The result type should be indexed.
     */
    public function testIndexResultType()
    {

        // Add an exhibit to the index.
        $exhibit = $this->_exhibit(true);

        // Get the Solr document for the exhibit.
        $document = $this->_getRecordDocument($exhibit);

        // Should index the URL.
        $this->assertEquals('Exhibit', $document->resulttype);

    }


    /**
     * The title should be indexed.
     */
    public function testIndexTitle()
    {

        // Add an exhibit called "title".
        $exhibit = $this->_exhibit(true, 'title');

        // Get the Solr document for the exhibit.
        $document = $this->_getRecordDocument($exhibit);

        // Should index the URL.
        $this->assertEquals('title', $document->title);

    }


    /**
     * The title should be indexed.
     */
    public function testIndexDescription()
    {

        // Add an exhibit with a description.
        $exhibit = $this->_exhibit(true);
        $exhibit->description = 'description';
        $exhibit->save();

        // Get the Solr document for the exhibit.
        $document = $this->_getRecordDocument($exhibit);

        // Get the name of the `description` Solr key.
        $indexKey = $this->_getAddonKey($exhibit, 'description');

        // Should index the text field.
        $this->assertEquals('description', $document->$indexKey);

    }


}
