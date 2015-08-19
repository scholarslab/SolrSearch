<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


define('SOLR_DIR', dirname(dirname(dirname(__FILE__))));
define('SOLR_TEST_DIR', SOLR_DIR.'/tests/phpunit');
define('OMEKA_DIR', dirname(dirname(SOLR_DIR)));

// Bootstrap Omeka.
require_once OMEKA_DIR.'/application/tests/bootstrap.php';

// Base test case.
require_once 'cases/SolrSearch_Case_Default.php';

// Load other files.
require_once SOLR_DIR . '/helpers/SolrSearch_Helpers_Index.php';
