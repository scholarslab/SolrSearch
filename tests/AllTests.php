<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 *
 * PHP version 5
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package     omeka
 * @subpackage  fedoraconnector
 * @author      Scholars' Lab <>
 * @author      Ethan Gruber <ewg4x@virginia.edu>
 * @author      Adam Soroka <ajs6f@virginia.edu>
 * @author      Wayne Graham <wayne.graham@virginia.edu>
 * @author      Eric Rochester <err8n@virginia.edu>
 * @copyright   2010 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 * @version     $Id$
 * @link        http://omeka.org/add-ons/plugins/SolrSearch/
 * @tutorial    tutorials/omeka/SolrSearch.pkg
 */

require_once 'SolrSearch_Test_AppTestCase.php';

/**
 * Test suite for SolrSearch.
 *
 * @package   Omeka
 * @copyright 2010 The Board and Visitors of the University of Virginia
 */
class SolrSearch_AllTests extends PHPUnit_Framework_TestSuite
{
    /**
     * This constructos the test suite for the SolrSearch plugin.
     *
     * @return SolrSearch_AllTests The test suites.
     */
    public static function suite() {

        $suite = new SolrSearch_AllTests('SolrSearch Tests');

        $testCollector = new PHPUnit_Runner_IncludePathTestCollector(
            array(
                dirname(__FILE__) . '/unit',
                dirname(__FILE__) . '/SolrSearch'
            )
        );
        $suite->addTestFiles($testCollector->collectTests());

        return $suite;

    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
