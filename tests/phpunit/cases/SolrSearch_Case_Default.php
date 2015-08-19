<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_Case_Default extends Omeka_Test_AppTestCase
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
        $this->fieldTable       = $this->db->getTable('SolrSearchField');
        $this->elementSetTable  = $this->db->getTable('ElementSet');
        $this->elementTable     = $this->db->getTable('Element');
        $this->itemTypeTable    = $this->db->getTable('ItemType');

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

        // Parse the config file.
        $this->config = new Zend_Config_Ini(SOLR_TEST_DIR.'/solr.ini');

        // Apply the testing values.
        set_option('solr_search_port',  $this->config->port);
        set_option('solr_search_host',  $this->config->server);
        set_option('solr_search_core',  $this->config->core);

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
    protected function _clearFieldMappings()
    {
        $this->db->query(<<<SQL
        DELETE FROM {$this->db->prefix}solr_search_fields;
SQL
);
    }


    /**
     * Inject and return a mock `Omeka_Job_Dispatcher_Default`.
     *
     * @return Omeka_Job_Dispatcher_Default PHPUnit mock.
     */
    protected function _mockJobDispatcher()
    {

        // Create a testing-double job dispatcher.
        $jobs = $this->getMockBuilder('Omeka_Job_Dispatcher_Default')
            ->disableOriginalConstructor()->getMock();

        // Inject the mock.
        Zend_Registry::set('job_dispatcher', $jobs);

        return $jobs;

    }


    // RECORD FIXTURES
    // ------------------------------------------------------------------------


    /**
     * Create an item.
     *
     * @param boolean $public True if the item is public.
     * @param string $title The Dublin Core "Title".
     * @return Item
     */
    protected function _item($public=true, $title='Test Title')
    {
        return insert_item(array('public' => true), array(
            'Dublin Core' => array(
                'Title' => array(
                    array('text' => $title, 'html' => false)
                )
            )
        ));
    }


    /**
     * Create a field.
     *
     * @param string $slug The facet slug.
     * @param string $label The facet label.
     * @return SolrSearchField
     */
    protected function _field($slug, $label='Test Label')
    {

        $field = new SolrSearchField();
        $field->slug  = $slug;
        $field->label = $label;

        $field->save();
        return $field;

    }


    /**
     * Create a Simple Pages page.
     *
     * @param boolean $public True if the page is public.
     * @param string $title The exhibit title.
     * @param string $slug The exhibit slug.
     * @return SimplePagesPage
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
     * Create an exhibit.
     *
     * @param boolean $public True if the exhibit is public.
     * @param string $title The exhibit title.
     * @param string $slug The exhibit slug.
     * @return Exhibit
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
     * Create an exhibit page.
     *
     * @param Exhibit $exhibit The parent exhibit.
     * @param string $title The page title.
     * @param string $slug The page slug.
     * @param string $layout The layout template.
     * @param integer $order The page order.
     * @return ExhibitPage
     */
    protected function _exhibitPage(
        $exhibit=null, $title='Test Title', $slug='test-slug',
        $layout='text', $order=1
    ) {

        // Create a parent exhibit if none is passed.
        if (is_null($exhibit)) $exhibit = $this->_exhibit();

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
     * Create an exhibit page block.
     *
     * @param ExhibitPage $page The parent page.
     * @param string $text The entry content.
     * @param integer $order The entry order.
     * @return ExhibitPageBlock
     */
    protected function _exhibitBlock($page=null, $text='Test text.') {

        // Create a parent page if none is passed.
        if (is_null($page)) $page = $this->_exhibitPage();

        $block = new ExhibitPageBlock;

        $block->page_id = $page->id;
        $block->text    = $text;
        $block->layout  = 'text';

        $block->save();
        return $block;

    }


    /**
     * Create an element.
     *
     * @param string $elementSetName The name of the parent element set.
     * @param string $name The element name.
     * @return Element
     */
    protected function _element(
        $elementSetName='Item Type Metadata', $name='Test Element'
    ) {

        // Get the parent element set.
        $elementSet = $this->elementSetTable->findByName($elementSetName);

        $element = new Element();
        $element->element_set_id = $elementSet->id;
        $element->name = $name;

        $element->save();
        return $element;

    }


    /**
     * Reload a record.
     *
     * @param Omeka_Record_AbstractRecord $record A record to reload.
     * @return Omeka_Record_AbstractRecord The reloaded record.
     */
    protected function _reload($record)
    {
        return $record->getTable()->find($record->id);
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
    protected function _getAddonKey($record, $field)
    {

        // Spin up a manager and indexer.
        $mgr = new SolrSearch_Addon_Manager($this->db);
        $idx = new SolrSearch_Addon_Indexer($this->db);
        $mgr->parseAll();

        // Return the key used to store the field on the Solr document.
        return $idx->makeSolrName($mgr->findAddonForRecord($record), $field);

    }


    /**
     * Get the key for an element's tokenized field on a Solr document.
     *
     * @param string $set The element set name.
     * @param string $element The element name.
     * @return string
     */
    protected function _getElementTextKey($set, $element)
    {
        $field = $this->fieldTable->findByElementName($set, $element);
        return $field->indexKey();
    }


    /**
     * Get the key for an element's un-tokenized field on a Solr document.
     *
     * @param string $set The element set name.
     * @param string $element The element name.
     * @return string
     */
    protected function _getElementStringKey($set, $element)
    {
        $field = $this->fieldTable->findByElementName($set, $element);
        return $field->facetKey();
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


    /**
     * Get a facet URL for use in XPath queries.
     *
     * @param string $field The field name.
     * @param string $value The facet value.
     * @param string The XPath-compliant URL.
     */
    protected function _getFacetLink($field, $value)
    {
        return htmlspecialchars_decode(
            SolrSearch_Helpers_Facet::addFacet($field, $value)
        );
    }


    /**
     * Assert the presence of a facet link.
     *
     * @param string $url The facet URL.
     * @param string $text The facet value.
     */
    protected function _assertFacetLink($url, $value)
    {
        $this->assertXpathContentContains(
            "//a[@href='$url'][@class='facet-value']", $value
        );
    }


    /**
     * Assert the absence of a facet link.
     *
     * @param string $url The facet URL.
     */
    protected function _assertNotFacetLink($url)
    {
        $this->assertNotXpath("//a[@href='$url'][@class='facet-value']");
    }


    /**
     * Assert the presence of a result link.
     *
     * @param string $url The result URL.
     * @param string $title The result title.
     */
    protected function _assertResultLink($url, $title)
    {
        $this->assertXpathContentContains(
            "//a[@href='$url'][@class='result-title']", $title
        );
    }


    /**
     * Assert the absence of a result link.
     *
     * @param string $url The result URL.
     */
    protected function _assertNotResultLink($url)
    {
        $this->assertNotXpath("//a[@href='$url'][@class='result-title']");
    }


}
