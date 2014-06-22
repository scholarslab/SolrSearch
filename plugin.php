<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


if (!defined('SOLR_DIR')) define('SOLR_DIR', dirname(__FILE__));

// Plugin manager class:
require_once SOLR_DIR.'/SolrSearchPlugin.php';

// Solr PHP Client library:
require_once SOLR_DIR.'/lib/solr-php-client/Document.php';
require_once SOLR_DIR.'/lib/solr-php-client/Exception.php';
require_once SOLR_DIR.'/lib/solr-php-client/Response.php';
require_once SOLR_DIR.'/lib/solr-php-client/Service.php';

// SolrSearch utility classes:
require_once SOLR_DIR.'/lib/SolrSearch/Addon/Addon.php';
require_once SOLR_DIR.'/lib/SolrSearch/Addon/Config.php';
require_once SOLR_DIR.'/lib/SolrSearch/Addon/Field.php';
require_once SOLR_DIR.'/lib/SolrSearch/Addon/Indexer.php';
require_once SOLR_DIR.'/lib/SolrSearch/Addon/Manager.php';
require_once SOLR_DIR.'/lib/SolrSearch/Utils.php';
require_once SOLR_DIR.'/lib/SolrSearch/DbPager.php';

// Helpers:
require_once SOLR_DIR.'/helpers/SolrSearch_Helpers_View.php';
require_once SOLR_DIR.'/helpers/SolrSearch_Helpers_Index.php';
require_once SOLR_DIR.'/helpers/SolrSearch_Helpers_Facet.php';

// Forms:
require_once SOLR_DIR.'/forms/SolrSearch_Form_Server.php';
require_once SOLR_DIR.'/forms/SolrSearch_Form_Results.php';
require_once SOLR_DIR.'/forms/SolrSearch_Form_Reindex.php';

// Jobs:
require_once SOLR_DIR.'/jobs/SolrSearch_Job_Reindex.php';

$solr = new SolrSearchPlugin();
$solr->setUp();
