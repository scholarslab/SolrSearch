<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


// {{{ constants

if (!defined('SOLR_SEARCH_PLUGIN_VERSION')) {
    define('SOLR_SEARCH_PLUGIN_VERSION', get_plugin_ini('SolrSearch', 'version'));
}

if (!defined('SOLR_SEARCH_PLUGIN_DIR')) {
    define('SOLR_SEARCH_PLUGIN_DIR', dirname(__FILE__));
}

// }}}

// Plugin manager:
require_once SOLR_SEARCH_PLUGIN_DIR . '/SolrSearchPlugin.php';

// Solr PHP Client library:
require_once SOLR_SEARCH_PLUGIN_DIR . '/lib/solr-php-client/Apache/Solr/Document.php';
require_once SOLR_SEARCH_PLUGIN_DIR . '/lib/solr-php-client/Apache/Solr/Exception.php';
require_once SOLR_SEARCH_PLUGIN_DIR . '/lib/solr-php-client/Apache/Solr/Response.php';
require_once SOLR_SEARCH_PLUGIN_DIR . '/lib/solr-php-client/Apache/Solr/Service.php';

// SolrSearch plugin libraries:
require_once SOLR_SEARCH_PLUGIN_DIR . '/lib/SolrSearch/QueryManager.php';
require_once SOLR_SEARCH_PLUGIN_DIR . '/lib/SolrSearch/Addon.php';
require_once SOLR_SEARCH_PLUGIN_DIR . '/lib/SolrSearch/Utils.php';
require_once SOLR_SEARCH_PLUGIN_DIR . '/lib/SolrSearch/DbPager.php';

// Theme helpers:
require_once SOLR_SEARCH_PLUGIN_DIR . '/helpers/SolrSearch_Helpers_View.php';
require_once SOLR_SEARCH_PLUGIN_DIR . '/helpers/SolrSearch_Helpers_Query.php';
require_once SOLR_SEARCH_PLUGIN_DIR . '/helpers/SolrSearch_Helpers_Index.php';

// Forms:
require_once SOLR_SEARCH_PLUGIN_DIR . '/forms/SolrSearch_Form_Server.php';
require_once SOLR_SEARCH_PLUGIN_DIR . '/forms/SolrSearch_Form_Facet.php';
require_once SOLR_SEARCH_PLUGIN_DIR . '/forms/SolrSearch_Form_Highlight.php';
require_once SOLR_SEARCH_PLUGIN_DIR . '/forms/SolrSearch_Form_Reindex.php';

new SolrSearchPlugin();
