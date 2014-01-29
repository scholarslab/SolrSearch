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
        $this->facetsTable     = $this->db->getTable('SolrSearchFacet');
        $this->elementSetTable = $this->db->getTable('ElementSet');
        $this->elementTable    = $this->db->getTable('Element');

        // Apply `solr.ini` values.
        $this->_applyTestingOptions();

    }


    // STATE MANAGEMENT
    // ------------------------------------------------------------------------


    /**
     * Apply options defined in the `solr.ini` file.
     */
    protected function _applyTestingOptions()
    {
        if (file_exists(SOLR_TEST_DIR.'/solr.ini')) {
            $this->config = new Zend_Config_Ini(SOLR_TEST_DIR.'/solr.ini');
            set_option('solr_search_server',    $this->config->server);
            set_option('solr_search_port',      $this->config->port);
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

        // Break if plugin is installed.
        if (plugin_is_active($pluginName)) return;

        try {
            $this->helper->setUp($pluginName);
        } catch (Exception $e) {
            $this->markTestSkipped("Plugin $pluginName can't be installed.");
        }

    }


    // RECORD MOCKS
    // ------------------------------------------------------------------------


    /**
     * Create an exhibit with pages and entries.
     */
    protected function _createExhibit($exhibit)
    {
        $e = new Exhibit();
        $e->title       = property_exists($exhibit, 'title') ? $exhibit->title : null;
        $e->description = property_exists($exhibit, 'description') ? $exhibit->description : null;
        $e->public      = property_exists($exhibit, 'public') ? $exhibit->public : true;
        $e->public      = $e->public ? 1 : 0;
        $e->save();

        if (property_exists($exhibit, 'tags')) {
            $e->addTags($exhibit->tags, $this->user);
            $e->save();
        }

        $j = 1;
        foreach ($exhibit->pages as $page) {
            $p = new ExhibitPage();
            $p->title      = property_exists($page, 'title') ? $page->title : null;
            $p->slug       = "exhibit-page-$j";
            $p->exhibit_id = $e->id;
            $p->order      = $j;
            $p->layout     = 'horizontal';
            $p->save();

            $entries = property_exists($page, 'entries') ? $page->entries : array();
            $k = 1;
            foreach ($entries as $entry) {

                $item = $this->_item(
                    property_exists($entry, 'title') ? $entry->title : null,
                    property_exists($entry, 'subject') ? $entry->subject : null
                );

                $pe = new ExhibitPageEntry();
                $pe->item_id = $item->id;
                $pe->page_id = $p->id;
                $pe->order   = $k;
                $pe->text    = property_exists($entry, 'text') ? $entry->text : null;
                $pe->caption = property_exists($entry, 'caption') ? $entry->caption : null;
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
    protected function _loadModels()
    {
        $filename = SOLR_TEST_DIR . '/fixtures/exhibits.json';
        $exhibits = json_decode(file_get_contents($filename));

        foreach ($exhibits as $exhibit) {
            $this->_createExhibit($exhibit);
        }
    }


    /**
     * Create an item.
     *
     * @param string $title     The Dublin Core "Title".
     * @param string $subject   The Dublin Core "Subject".
     */
    protected function _item($title=null, $subject=null)
    {
        return insert_item(array(), array('Dublin Core' => array(
            'Title'     => array(array('text' => $title,    'html' => false)),
            'Subject'   => array(array('text' => $subject,  'html' => false))
        )));
    }


    // DATA HELPERS
    // ------------------------------------------------------------------------


    /**
     * Get a facet mapping row by name.
     *
     * @param string $name The facet name.
     * @return SolrSearchFacet
     */
    protected function _getFacetByName($name)
    {
        return $this->facetsTable->findBySql(
            'name=?', array($name), true
        );
    }


    // CUSTOM ASSERTIONS
    // ------------------------------------------------------------------------


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


}
