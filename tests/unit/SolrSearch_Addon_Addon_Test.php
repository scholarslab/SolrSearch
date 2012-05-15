<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * @package     omeka
 * @subpackage  SolrSearch
 * @author      Scholars' Lab <>
 * @author      Eric Rochester <erochest@virginia.edu>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class SolrSearch_Addon_Addon_Test extends SolrSearch_Test_AppTestCase
{

    public function setUp()
    {
        $this->setUpPlugin();

        $this->mgr = new SolrSearch_Addon_Manager($this->db);
        $this->mgr->parseAll();
    }

    public function testHasFlag()
    {
        $this->assertNotEmpty($this->mgr->addons);
        foreach ($this->mgr->addons as $addon) {
            $this->assertTrue($addon->hasFlag(), $addon->name);
        }
    }

}

