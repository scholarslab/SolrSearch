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

// Solr PHP Client library
require_once 'lib/Document.php';
require_once 'lib/Exception.php';
require_once 'lib/Response.php';
require_once 'lib/Service.php';

// SolrSearch utility classes
require_once 'lib/SolrSearch/QueryManager.php';
require_once 'lib/SolrSearch/Addon.php';
require_once 'lib/SolrSearch/Utils.php';

/*
 * Custom Theme Helpers. They're imported from the helpers file, which appears 
 * to get more love.
 */

require_once SOLR_SEARCH_PLUGIN_DIR . '/helpers/SolrSearch_ViewHelpers.php';
require_once SOLR_SEARCH_PLUGIN_DIR . '/helpers/SolrSearch_QueryHelpers.php';
require_once SOLR_SEARCH_PLUGIN_DIR . '/helpers/SolrSearch_IndexHelpers.php';
require_once SOLR_SEARCH_PLUGIN_DIR . '/SolrSearchPlugin.php';

new SolrSearchPlugin();

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
