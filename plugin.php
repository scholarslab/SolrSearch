<?php
define('SOLR_SEARCH_PLUGIN_VERSION', get_plugin_ini('SolrSearch', 'version'));
define('SOLR_SERVER', get_plugin_ini('SolrSearch', 'solr_server'));
define('SOLR_PORT', get_plugin_ini('SolrSearch', 'solr_port'));
define('SOLR_CORE', get_plugin_ini('SolrSearch', 'solr_core'));
define('SOLR_ROWS', get_plugin_ini('SolrSearch', 'solr_rows'));

require_once 'lib/Document.php';
require_once 'lib/Response.php';
require_once 'lib/Service.php';

add_plugin_hook('install', 'solr_search_install');
add_plugin_hook('uninstall', 'solr_search_uninstall');
add_plugin_hook('define_routes', 'solr_search_define_routes');
//add_plugin_hook('config_form', 'solr_search_config_form');
//add_plugin_hook('config', 'solr_search_config');

function solr_search_install()
{
	set_option('solr_search_plugin_version', SOLR_SEARCH_PLUGIN_VERSION);

 // Create the table.
/*    $db = get_db();
    $sql = "
    CREATE TABLE IF NOT EXISTS `$db->SolrSearch` (
      `id` int(10) unsigned NOT NULL auto_increment,
      `server` tinytext collate utf8_unicode_ci NOT NULL,
      `port` int(10) unsigned NOT NULL,
      `core` tinytext collate utf8_unicode_ci NOT NULL,
      PRIMARY KEY  (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
    $db->query($sql);*/
}

function solr_search_uninstall()
{
	delete_option('solr_search_plugin_version');

	// Drop the table.
	/*    $db = get_db();
	    $sql = "DROP TABLE IF EXISTS `$db->SolrSearch`";
	    $db->query($sql);*/
}

/*function solr_search_config_form()
{
    include 'config_form.php';
}*/

/**
 * Define the routes.
 * 
 * @param Zend_Controller_Router_Rewrite
 */
function solr_search_define_routes($router)
{
	$searchResultsRoute = new Zend_Controller_Router_Route('results', 
                                                 array('controller' => 'search', 
                                                       'action'     => 'results', 
                                                       'module'     => 'solr-search'));
	$router->addRoute('solr_search_results_route', $searchResultsRoute);
}

function solr_search($buttonText = "Search", $formProperties=array('id'=>'simple-search'), $uri = '/omeka/solr-search/search/results/page/1') 
{ 
    $formProperties['action'] = $uri;
    $formProperties['method'] = 'get';
    $html  = '<form ' . _tag_attributes($formProperties) . '>' . "\n";
    $html .= '<fieldset>' . "\n\n";
    $html .= __v()->formText('q', html_escape($_REQUEST['q']), array('name'=>'textinput','class'=>'textinput'));
    $html .= '</fieldset>' . "\n\n";
    $html .= '</form>';
    return $html;
}