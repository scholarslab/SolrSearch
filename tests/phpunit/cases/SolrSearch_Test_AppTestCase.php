<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_Test_AppTestCase extends Omeka_Test_AppTestCase
{


    /**
     * Install SolrSearch and prepare the database.
     */
    public function setUp()
    {

        parent::setUp();

        // Create and authenticate user.
        $this->user = $this->db->getTable('User')->find(1);
        $this->_authenticateUser($this->user);

        // Install up SolrSearch.
        $this->helper = new Omeka_Test_Helper_Plugin;
        $this->helper->setUp('SolrSearch');

        // Get tables.
        $this->facetTable       = $this->db->getTable('SolrSearchFacet');
        $this->elementSetTable  = $this->db->getTable('ElementSet');
        $this->elementTable     = $this->db->getTable('Element');

        // Apply `solr.ini` values.
        $this->_applyTestingOptions();

        // Connect to Solr.
        $this->solr = SolrSearch_Helpers_Index::connect();

    }


    /**
     * Clear the Solr index.
     */
    public function tearDown()
    {
        try {
            SolrSearch_Helpers_Index::deleteAll();
        } catch (Exception $e) {};
        parent::tearDown();
    }


    // ENVIRONMENT
    // ------------------------------------------------------------------------


    /**
     * Apply options defined in the `solr.ini` file.
     */
    protected function _applyTestingOptions()
    {
        if (file_exists(SOLR_TEST_DIR.'/solr.ini')) {
            $this->config = new Zend_Config_Ini(SOLR_TEST_DIR.'/solr.ini');
            set_option('solr_search_port',      $this->config->port);
            set_option('solr_search_server',    $this->config->server);
            set_option('solr_search_core',      $this->config->core);
        }
    }


    /**
     * Install the passed plugin. If the installation fails, skip the test.
     *
     * @param string $pluginName The plugin name.
     */
    protected function _installPluginOrSkip($pluginName)
    {

        // Break if plugin is already installed. (Necessary to prevent errors
        // caused by trying to re-activate the Exhibit Builder ACL.)
        if (plugin_is_active($pluginName)) return;

        try {
            $this->helper->setUp($pluginName);
        } catch (Exception $e) {
            $this->markTestSkipped("Plugin $pluginName can't be installed.");
        }

    }


    /**
     * Delete all existing facet mappings.
     */
    protected function _clearFacetMappings()
    {
        $this->db->query(<<<SQL
        DELETE FROM {$this->db->prefix}solr_search_facets WHERE 1=1
SQL
);
    }


    // LEGACY FIXTURES
    // ------------------------------------------------------------------------


    /**
     * Create an exhibit with pages and entries.
     *
     * @param array $exhibit The raw exhibit fixture array.
     */
    protected function _createExhibit($exhibit)
    {
        $e = new Exhibit();
        $e->title       = array_key_exists('title', $exhibit) ? $exhibit['title'] : null;
        $e->description = array_key_exists('description', $exhibit) ? $exhibit['description'] : null;
        $e->public      = array_key_exists('public', $exhibit) ? $exhibit['public'] : true;
        $e->public      = $e->public ? 1 : 0;
        $e->save();

        if (array_key_exists('tags', $exhibit)) {
            $e->addTags($exhibit['tags'], $this->user);
            $e->save();
        }

        $j = 1;
        foreach ($exhibit['pages'] as $page) {
            $p = new ExhibitPage();
            $p->title      = array_key_exists('title', $page) ? $page['title'] : null;
            $p->slug       = "exhibit-page-$j";
            $p->exhibit_id = $e->id;
            $p->order      = $j;
            $p->layout     = 'horizontal';
            $p->save();

            $entries = array_key_exists('entries', $page) ? $page['entries'] : array();
            $k = 1;
            foreach ($entries as $entry) {

                $item = $this->_item(
                    array_key_exists('title', $entry) ? $entry['title'] : null,
                    array_key_exists('subject', $entry) ? $entry['subject'] : null
                );

                $pe = new ExhibitPageEntry();
                $pe->item_id = $item->id;
                $pe->page_id = $p->id;
                $pe->order   = $k;
                $pe->text    = array_key_exists('text', $entry) ? $entry['text'] : null;
                $pe->caption = array_key_exists('caption', $entry) ? $entry['caption'] : null;
                $pe->save();

                $k++;
            }

            $j++;
        }

        return $e;

    }


    /**
     * Install Exhibit Buidler fixtures.
     */
    protected function _loadExhibits()
    {

        // Parse the JSON file.
        $fixture = Zend_Json::decode(file_get_contents(
            SOLR_TEST_DIR . '/fixtures/exhibits.json'
        ));

        // Install each of the exhibits.
        foreach ($fixture['exhibits'] as $exhibit) {
            $this->_createExhibit($exhibit);
        }

    }


    // RECORD FIXTURES
    // ------------------------------------------------------------------------


    /**
     * Create an item.
     *
     * @param string $title The Dublin Core "Title".
     * @param string $subject The Dublin Core "Subject".
     * $return Item
     */
    protected function _item($title=null, $subject=null)
    {
        return insert_item(array(), array('Dublin Core' => array(

            'Title' => array(array(
                'text' => $title,
                'html' => false
            )),

            'Subject' => array(array(
                'text' => $subject,
                'html' => false
            ))

        )));
    }


    /**
     * Create a Simple Pages page.
     *
     * @param boolean $public True if the page is public.
     * $return SimplePagesPage
     */
    protected function _simplePage(
        $public=true, $title='Test Title', $slug='test-slug'
    ) {

        $page = new SimplePagesPage;

        // Set parent user and public/private.
        $page->created_by_user_id = current_user()->id;
        $page->is_published = $public;

        // Set text fields.
        $page->slug  = $slug;
        $page->title = $title;

        $page->save();
        return $page;

    }


    /**
     * Create an Exhibit.
     *
     * @param boolean $public True if the exhibit is public.
     * @param string $title The exhibit title.
     * @param string $slug The exhibit slug.
     * $return Exhibit
     */
    protected function _exhibit(
        $public=true, $title='Test Title', $slug='test-slug'
    ) {

        $exhibit = new Exhibit;

        $exhibit->public    = $public;
        $exhibit->slug      = $slug;
        $exhibit->title     = $title;

        $exhibit->save();
        return $exhibit;

    }


    /**
     * Create an Exhibit page.
     *
     * @param Exhibit $exhibit The parent exhibit.
     * @param string $title The page title.
     * @param string $layout The layout template.
     * @param string $slug The page slug.
     * @param integer $order The page order.
     * $return ExhibitPage
     */
    protected function _exhibitPage(
        $exhibit, $title='Test Title', $layout='text',
        $slug='test-slug', $order=1
    ) {

        $page = new ExhibitPage;

        $page->exhibit_id   = $exhibit->id;
        $page->slug         = $slug;
        $page->layout       = $layout;
        $page->title        = $title;
        $page->order        = $order;

        $page->save();
        return $page;

    }


    /**
     * Create an Exhibit page entry.
     *
     * @param ExhibitPage $page The parent page.
     * @param string $text The entry content.
     * @param integer $order The entry order.
     * $return ExhibitPage
     */
    protected function _exhibitEntry($page, $text='Test text.', $order=1)
    {

        $entry = new ExhibitPageEntry;

        $entry->page_id = $page->id;
        $entry->text    = $text;
        $entry->order   = $order;

        $entry->save();
        return $entry;

    }


    // SOLR HELPERS
    // ------------------------------------------------------------------------


    /**
     * Count the total number of document in the Solr index.
     *
     * @return integer
     */
    protected function _countSolrDocuments()
    {
        return $this->solr->search("*:*")->response->numFound;
    }


    /**
     * Get the key used on Solr documents for a field on a given record.
     *
     * @param Omeka_Record_AbstractRecord $record The record.
     * @param string $field The field.
     * @return string
     */
    protected function _getAddonSolrKey($record, $field)
    {

        // Spin up a manager and indexer.
        $mgr = new SolrSearch_Addon_Manager($this->db);
        $idx = new SolrSearch_Addon_Indexer($this->db);
        $mgr->parseAll();

        // Return the key used to store the field on the Solr document.
        return $idx->makeSolrName($mgr->findAddonForRecord($record), $field);

    }


    /**
     * Get the `name` (used as the key value on Solr documents) for a facet.
     *
     * @param string $elementSetName The element set name.
     * @param string $elementName The element name.
     * @return string
     */
    protected function _getElementSolrKey($elementSetName, $elementName)
    {

        // Get the element.
        $element = $this->elementTable->findByElementSetNameAndElementName(
            $elementSetName, $elementName
        );

        // Get the subject and source facets.
        return $this->facetTable->findByElement($element)->name;

    }


    /**
     * Search for a record by id.
     *
     * @param Omeka_Record_AbstractRecord $record The page.
     * @return Apache_Solr_Response
     */
    protected function _searchForRecord($record)
    {

        // Get a Solr id for the record.
        $id = get_class($record) . "_{$record->id}";

        // Query for the document.
        return $this->solr->search("id:$id");

    }


    /**
     * Get the individual Solr document for a record.
     *
     * @param Omeka_Record_AbstractRecord $record The page.
     * @return Apache_Solr_Document
     */
    protected function _getRecordDocument($page)
    {
        return $this->_searchForRecord($page)->response->docs[0];
    }


    // CUSTOM ASSERTIONS
    // ------------------------------------------------------------------------


    /**
     * Assert that a record is indexed in Solr.
     *
     * @param Omeka_Record_AbstractRecord $record The page.
     */
    protected function _assertRecordInSolr($record)
    {

        // Query for the document.
        $result = $this->_searchForRecord($record);

        // Solr document should exist.
        $this->assertEquals(1, $result->response->numFound);

    }


    /**
     * Assert that a record is _not_ indexed in Solr.
     *
     * @param Omeka_Record_AbstractRecord $record The page.
     */
    protected function _assertNotRecordInSolr($record)
    {

        // Query for the document.
        $result = $this->_searchForRecord($record);

        // Solr document should exist.
        $this->assertEquals(0, $result->response->numFound);

    }


    /**
     * Assert that a form error was displayed for an input.
     *
     * @param string $name The `name` attribute of the input with the error.
     * @param string $element The input element type.
     */
    protected function _assertFormError($name, $element='input')
    {
        $this->assertXpath(
            "//{$element}[@name='$name']
            /following-sibling::ul[@class='error']"
        );
    }


}
