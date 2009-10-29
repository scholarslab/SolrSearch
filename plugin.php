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

function solr_search($buttonText = "Search", $formProperties=array('id'=>'simple-search'), $uri = 'results') 
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

/*function solr_paginate($results, $q, $start, $limit, $page)
	{
		$total = (int) $results->response->numFound;
		$start_doc = $start + 1;
		$total_pages = ceil($total / 10);
		$end = $page * $limit;
		$next = $page + 1;
		if ($page > 1)
		{
			$previous = $page - 1;
		}
		else{
			$previous = 0;
		}
		$current = $start / $limit + 1;

		$html .= '<div class="pagination" style="display:table;width:100%;">' . "\n" . '<div style="float:left">Results ' . $start_doc . ' - ' . $end . ' of ' . $total . '</div>' . "\n" . 
		'<div style="float:right"><ul class="pagination_list">' . "\n";

		//Display First/Previous links
		if ($page > 1)
			{ 
			$html .= '<li class="pagination_first"><a href="' . '?q=' . $q . '">First</a></li>'  . "\n" . '<li class="pagination_previous"><a href="' . '?q=' . $q . '&page=' . $previous . '">Previous</a></li>' . "\n";
		}

		//Display previous two pages if they meet numeric requirements
		if ($page - 2 > 0){
			$html .= '<li class="pagination_range"><a href="' . '?q=' . $q . '&page=' . ($page - 2) . '">' . ($page - 2) . '</a></li>' . "\n";
		}
		if ($page - 1 > 0){
			$html .= '<li class="pagination_range"><a href="' . '?q=' . $q . '&page=' . ($page - 1) . '">' . ($page - 1) . '</a></li>' . "\n";
		}

		//Display current page number
		$html .= '<li class="pagination_current">' . $page . '</li>';

		//Display next two pages if they meet numeric requirements	
		if ($page + 1 <= $total_pages){
			$html .= '<li class="pagination_range"><a href="' . '?q=' . $q . '&page=' . ($page + 1) . '">' . ($page + 1) . '</a></li>' . "\n";
		}
		if ($page + 2 <= $total_pages){
			$html .= '<li class="pagination_range"><a href="' . '?q=' . $q . '&page=' . ($page + 2) . '">' . ($page + 2) . '</a></li>' . "\n";
		}

		//Display Next/Last links
		if ($page < $total_pages)
			{ 
			$html .= '<li class="pagination_next"><a href="' . '?q=' . $q . '&page=' . $next . '">Next</a></li>'  . "\n" . '<li class="pagination_last"><a href="' . '?q=' . $q . '&page=' . $total_pages . '">Last</a></li>';
		}	
		$html .= '</ul>' . "\n" . '</div>' . "\n" . '</div>' . "\n";
	
		return $html;
	}*/
