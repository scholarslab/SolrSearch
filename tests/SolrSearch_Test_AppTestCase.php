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

require_once '../SolrSearchPlugin.php';

class SolrSearch_Test_AppTestCase extends Omeka_Test_AppTestCase
{

    private $_dbHelper;
    private $_todel;

    /**
     * Spin up the plugins and prepare the database.
     *
     * @return void.
     */
    public function setUpPlugin()
    {

        parent::setUp();

        // Create and authenticate user.
        $this->user = $this->db->getTable('User')->find(1);
        $this->_authenticateUser($this->user);

        // Set up SolrSearch.
        $plugin_broker = get_plugin_broker();
        $this->_addHooksAndFilters($plugin_broker, 'SolrSearch');
        $plugin_helper = new Omeka_Test_Helper_Plugin;
        $plugin_helper->setUp('SolrSearch');

        // Get database helper.
        $this->_dbHelper = Omeka_Test_Helper_Db::factory($this->core);

        // Get tables.
        $this->facetsTable     = $this->db->getTable('SolrSearchFacet');
        $this->elementSetTable = $this->db->getTable('ElementSet');
        $this->elementTable    = $this->db->getTable('Element');

        $this->_todel = array();

        // Retrieve the element for some DC fields.
        $this->_title = $this->elementTable
            ->findByElementSetNameAndElementName('Dublin Core', 'Title');
        $this->_subject = $this->elementTable
            ->findByElementSetNameAndElementName('Dublin Core', 'Subject');
    }

    protected function _setUpNamedPlugin($name, $table=null)
    {
        $dbLoaded = false;

        $broker = get_plugin_broker();
        $this->_addHooksAndFilters($broker, $name);

        $helper = new Omeka_Test_Helper_Plugin();
        $helper->setUp($name);

        if (!is_null($table)) {
            $tname   = $this->db->getTable($table)->getTableName();
            $results = $this->db->fetchAll('SHOW TABLES;', array(), Zend_Db::FETCH_NUM);
            foreach ($results as $row) {
                if ($row[0] == $tname) {
                    $dbLoaded = true;
                    break;
                }
            }
        }

        return $dbLoaded;
    }

    protected function setUpExhibitBuilder()
    {
        $dbLoaded = $this->_setUpNamedPlugin('ExhibitBuilder', 'ExhibitSection');
        try {
            exhibit_builder_setup_acl(Omeka_Context::getInstance()->acl);
        } catch (Exception $e) {
        }
        if (!$dbLoaded) {
            exhibit_builder_install();
        }
    }

    protected function setUpSimplePages()
    {
        $this->_setUpNamedPlugin('SimplePages', 'SimplePagesPage');
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

        $i = 1;
        foreach ($exhibit->sections as $section) {
            $s = new ExhibitSection();
            $s->title       = property_exists($section, 'title') ? $section->title : null;
            $s->description = property_exists($section, 'description') ? $section->description : null;
            $s->exhibit_id  = $e->id;
            $s->order       = $i;
            $s->save();

            $j = 1;
            foreach ($section->pages as $page) {
                $p = new ExhibitPage();
                $p->title      = property_exists($page, 'title') ? $page->title : null;
                $p->slug       = "exhibit-page-$j";
                $p->section_id = $s->id;
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

            $i++;
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
     * Test helpers.
     */

    public function tearDown()
    {
        parent::tearDown();

        if (is_array($this->_todel)) {
            foreach ($this->_todel as $todel) {
                try {
                    $todel->delete();
                } catch (Exception $e) {
                }
            }
            $this->_todel = array();
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
    protected function addElementText($item, $element, $text, $html=0)
    {
        $etext = new ElementText;

        $etext->setText($text);
        $etext->html           = $html;
        $etext->element_id     = $element->id;
        $etext->record_id      = $item->id;
        $etext->record_type_id = 2;
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
