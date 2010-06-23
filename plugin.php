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
add_plugin_hook('define_acl', 'solr_search_define_acl');
add_plugin_hook('admin_theme_header', 'solr_search_admin_header');
add_filter('admin_navigation_main', 'solr_search_admin_navigation');
//add_plugin_hook('config_form', 'solr_search_config_form');
//add_plugin_hook('config', 'solr_search_config');

function solr_search_install()
{
	$db = get_db();
	    
	// create for facet mapping
	$db->exec("CREATE TABLE IF NOT EXISTS `{$db->prefix}solr_search_facets` (
			`id` int(10) unsigned NOT NULL auto_increment,
			`element_id` int(10) unsigned,
			`name` tinytext collate utf8_unicode_ci NOT NULL,	      
			`element_set_id` int(10) unsigned,
			`is_facet` tinyint unsigned,
	       PRIMARY KEY  (`id`)
	       ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
	
	$elements = $db->getTable('Element')->findAll();
	
	foreach ($elements as $element){
		$data = array(	'element_id' => $element['id'],
						'name' => $element['name'],
						'element_set_id' => $element['element_set_id'],
						'is_facet' => 0);
		$db->insert('solr_search_facets', $data);
	}
	
	
	//var_dump($elementsToFacets);
}

function solr_search_uninstall()
{
	delete_option('solr_search_plugin_version');

	// Drop the table.
	$db = get_db();
	$sql = "DROP TABLE IF EXISTS `{$db->prefix}solr_search_facets`";
	$db->query($sql);
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

function solr_search_admin_navigation($tabs)
{
    if (get_acl()->checkUserPermission('SolrSearch_Facet', 'index')) {
        $tabs['Solr Facets'] = uri('solr-search/facets/');        
    }
    return $tabs;
}
	
function solr_search_define_acl($acl)
{
    $acl->loadResourceList(array('SolrSearch_Facet' => array('index', 'status')));
}

function solr_search_admin_header($request)
{
	if ($request->getModuleName() == 'vra-core-element-set') {
		echo '<link rel="stylesheet" href="' . html_escape(css('solr_search_main')) . '" />';
		//echo js('generic_xml_import_main');
    }
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