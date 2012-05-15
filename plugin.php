<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * SolrSearch Omeka Plugin setup file.
 *
 * This file will set up the SolrSearch plugin.
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
