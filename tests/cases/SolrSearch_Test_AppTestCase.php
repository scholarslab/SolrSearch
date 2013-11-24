<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Testing helper class.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2011 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class SolrSearch_Test_AppTestCase extends Omeka_Test_AppTestCase
{

    private $_todel;

    /**
     * Install SolrSearch and prepare the database.
     *
     * @return void.
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

        // Retrieve the element for some DC fields.
        $this->_title = $this->elementTable
            ->findByElementSetNameAndElementName('Dublin Core', 'Title');
        $this->_subject = $this->elementTable
            ->findByElementSetNameAndElementName('Dublin Core', 'Subject');

        // TODO|dev
        // Set `solr.ini` connection parameters.
        //if (file_exists(SOLR_TEST_DIR.'/solr.ini')) {
            //$config = new Zend_Config_Ini(SOLR_TEST_DIR.'/solr.ini');
            //set_option('solr_search_server',    $config->host);
            //set_option('solr_search_port',      $config->port);
            //set_option('solr_search_core',      $config->url);
        //}

    }

    protected function _setUpNamedPlugin($plugin)
    {
        try {
            $this->helper->setUp($plugin);
        } catch (Exception $e) {}
    }

    protected function setUpExhibitBuilder()
    {
        $this->_setUpNamedPlugin('ExhibitBuilder');
    }

    protected function setUpSimplePages()
    {
        $this->_setUpNamedPlugin('SimplePages');
    }

    protected function createExhibit($exhibit)
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

                $item = $this->__item(
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

    protected function loadModels()
    {
        $filename = SOLR_SEARCH_PLUGIN_DIR . '/tests/fixtures/exhibits.json';
        $exhibits = json_decode(file_get_contents($filename));

        foreach ($exhibits as $exhibit) {
            $this->createExhibit($exhibit);
        }
    }

    /**
     * Install SolrSearch.
     *
     * @return void.
     */
    public function _addHooksAndFilters($plugin_broker, $plugin_name)
    {
        $plugin_broker->setCurrentPluginDirName($plugin_name);
        new SolrSearchPlugin;
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
    protected function addElementText($item, $element, $text, $html=0)
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
     *
     * @return Omeka_record $item The item.
     */
    public function __item($title=null, $subject=null)
    {
        $item = new Item;
        $item->save();
        $this->_todel[] = $item;

        if (!is_null($title)) {
            $this->addElementText($item, $this->_title, $title);
        }
        if (!is_null($subject)) {
            $this->addElementText($item, $this->_subject, $subject);
        }

        return $item;
    }

}
