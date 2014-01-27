<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

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
require_once SOLR_DIR.'/lib/SolrSearch/QueryManager.php';
require_once SOLR_DIR.'/lib/SolrSearch/Utils.php';
require_once SOLR_DIR.'/lib/SolrSearch/DbPager.php';

// Theme helpers:
require_once SOLR_DIR.'/helpers/SolrSearch_Helpers_View.php';
require_once SOLR_DIR.'/helpers/SolrSearch_Helpers_Query.php';
require_once SOLR_DIR.'/helpers/SolrSearch_Helpers_Index.php';

// Forms:
require_once SOLR_DIR.'/forms/SolrSearch_Form_Server.php';
require_once SOLR_DIR.'/forms/SolrSearch_Form_Facet.php';
require_once SOLR_DIR.'/forms/SolrSearch_Form_Highlight.php';
require_once SOLR_DIR.'/forms/SolrSearch_Form_Reindex.php';

new SolrSearchPlugin();
