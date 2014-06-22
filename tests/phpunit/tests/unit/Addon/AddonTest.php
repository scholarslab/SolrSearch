<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchAddonTest_Addon extends SolrSearch_Case_Default
{

    public function setUp()
    {
        parent::setUp();
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

