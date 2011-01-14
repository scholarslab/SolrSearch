<?php

/**
 * Tests for SolrSearch plugin
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package    omeka
 * @subpackage SolrSearch
 * @author     "Scholars Lab"
 * @copyright  2010 The Board and Visitors of the University of Virginia
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @version    $Id$
 * @link       http://www.scholarslab.org
 *
 * PHP version 5
 *
 */


class SolrSearchTest extends Omeka_Test_AppTestCase
{
    protected $_search;
	
    public function setUp()
    {
        parent::setUp();
        
        $pluginHelper = new Omeka_Test_Helper_Plugin();
        $pluginHelper->setup('SolrSearch');
    }
    
    public function testInstall()
    {
        
    }
    
    public function testUninstall()
    {
        
    }
    
    public function testUpdateConfig()
    {
        
    }
    
    public function testRemoveIndex()
    {
        
    }
    
    public function testConfigForm()
    {
        
    }
    
    public function testBeforeDeleteItem()
    {
        
    }
    
    public function testAfterSaveItem()
    {
        
    }
    
    public function testRoutes()
    {
        
    }
	
    public function tearDown()
    {
        parent::tearDown();
    }
}

?>