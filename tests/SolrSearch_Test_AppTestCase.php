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
        $this->facetsTable = $this->db->getTable('SolrSearchFacet');
        $this->elementSetTable = $this->db->getTable('ElementSet');
        $this->elementTable = $this->db->getTable('Element');

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


    /**
     * Create an item.
     *
     * @return Omeka_record $item The item.
     */
    public function __item()
    {
        $item = new Item;
        $item->save();
        return $item;
    }

}
