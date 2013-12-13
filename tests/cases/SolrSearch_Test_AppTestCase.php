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


    /**
     * Apply options defined in the `solr.ini` file.
     */
    protected function _applyTestingOptions()
    {
        if (file_exists(SOLR_TEST_DIR.'/solr.ini')) {
            $this->config = new Zend_Config_Ini(SOLR_TEST_DIR.'/solr.ini');
            set_option('solr_search_server',    $this->config->host);
            set_option('solr_search_port',      $this->config->port);
            set_option('solr_search_core',      $this->config->url);
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
     * This cereates and element text and adds it to an item.
     *
     * @param Item    $item    The item to add the data to.
     * @param Element $element The element to add the text to.
     * @param string  $text    The text data.
     * @param bool    $html    Is the text really HTML? (Default is FALSE.)
     *
     * @return ElementText
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    protected function _addElementText($item, $element, $text, $html=0)
    {
        $etext = new ElementText;

        $etext->setText($text);
        $etext->html        = $html;
        $etext->element_id  = $element->id;
        $etext->record_id   = $item->id;
        $etext->record_type = 'Item';
        $etext->save();

        $item[$element->name] = $etext;

        return $etext;
    }


    /**
     * Create an item.
     */
    public function _item($title=null, $subject=null)
    {

        $item = new Item;
        $item->save();

        if (!is_null($title)) {
            $titleElement = $this->elementTable
                ->findByElementSetNameAndElementName('Dublin Core', 'Title');
            $this->_addElementText($item, $titleElement, $title);
        }
        if (!is_null($subject)) {
            $subjectElement = $this->elementTable
                ->findByElementSetNameAndElementName('Dublin Core', 'Subject');
            $this->_addElementText($item, $subjectElement, $subject);
        }

        return $item;

    }


}
