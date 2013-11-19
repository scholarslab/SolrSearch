<?php

define('SOLR_DIR', dirname(dirname(__FILE__)));
define('SOLR_TEST_DIR', SOLR_DIR.'/tests/phpunit');
define('OMEKA_DIR', dirname(dirname(SOLR_DIR)));

// Bootstrap Omeka.
require_once OMEKA_DIR.'/application/tests/bootstrap.php';

// Base test case.
require_once 'cases/SolrSearch_Test_AppTestCase.php';
