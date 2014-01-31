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


    /**
     * Create an item.
     *
     * @param string $title The Dublin Core "Title".
     * @param string $subject The Dublin Core "Subject".
     */
    protected function _item($title=null, $subject=null)
    {
        return insert_item(array(), array('Dublin Core' => array(
            'Title'     => array(array('text' => $title,    'html' => false)),
            'Subject'   => array(array('text' => $subject,  'html' => false))
        )));
    }


    /**
     * Assert that a form error was displayed for an input.
     *
     * @param string $name The `name` attribute of the input with the error.
     * @param string $element The input element type.
     */
    protected function _assertFormError($name, $element='input')
    {
        $this->assertXpath("//{$element}[@name='$name']
            /following-sibling::ul[@class='error']"
        );
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


}
