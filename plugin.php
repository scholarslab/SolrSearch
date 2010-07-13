<?php
define('SOLR_SEARCH_PLUGIN_VERSION', get_plugin_ini('SolrSearch', 'version'));
define('SOLR_SERVER', get_plugin_ini('SolrSearch', 'solr_server'));
define('SOLR_PORT', get_plugin_ini('SolrSearch', 'solr_port'));
define('SOLR_CORE', get_plugin_ini('SolrSearch', 'solr_core'));
define('SOLR_ROWS', get_plugin_ini('SolrSearch', 'solr_rows'));
define('SOLR_FACET_LIMIT', get_plugin_ini('SolrSearch', 'solr_facet_limit'));

require_once 'lib/Document.php';
require_once 'lib/Response.php';
require_once 'lib/Service.php';

add_plugin_hook('install', 'solr_search_install');
add_plugin_hook('uninstall', 'solr_search_uninstall');
add_plugin_hook('before_delete_item', 'solr_search_before_delete_item');
add_plugin_hook('after_save_item', 'solr_search_after_save_item');
add_plugin_hook('define_routes', 'solr_search_define_routes');
add_plugin_hook('define_acl', 'solr_search_define_acl');
add_plugin_hook('admin_theme_header', 'solr_search_admin_header');
add_plugin_hook('public_theme_header', 'solr_search_public_header');
add_filter('admin_navigation_main', 'solr_search_admin_navigation');
add_plugin_hook('config_form', 'solr_search_config_form');
add_plugin_hook('config', 'solr_search_config');

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
			`is_displayed` tinyint unsigned,			
			`is_sortable` tinyint unsigned,
	       PRIMARY KEY  (`id`)
	       ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
	
	$elements = $db->getTable('Element')->findAll();
	
	//add all element names to facet table for selection
	foreach ($elements as $element){
		$data = array(	'element_id' => $element['id'],
						'name' => $element['name'],
						'element_set_id' => $element['element_set_id'],
						'is_facet' => 0,
						'is_displayed' => 0,
						'is_sortable'=>0);
		$db->insert('solr_search_facets', $data);
	}
	
	//add public items to Solr index
	ProcessDispatcher::startProcess('SolrSearch_IndexAll', null, $args);
}

function solr_search_uninstall()
{
	// Drop the table.
	$db = get_db();
	$sql = "DROP TABLE IF EXISTS `{$db->prefix}solr_search_facets`";
	$db->query($sql);
	
	//delete Solr documents from index
	$solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);
	try {		
		$solr->deleteByQuery('*:*');
		$solr->commit();
		$solr->optimize(); 
	} catch ( Exception $err ) {
		echo $err->getMessage();
	}
}

// delete an item from the index
function solr_search_before_delete_item($item)
{
	$solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);
	try {		
		$solr->deleteByQuery('id:' . $item['id']);
		$solr->commit();
		$solr->optimize(); 
	} catch ( Exception $err ) {
		echo $err->getMessage();
	}
}

// reindex an item
function solr_search_after_save_item($item)
{
	$solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);	
	$db = get_db();
	$elementTexts = $db->getTable('ElementText')->findBySql('record_id = ?', array($item['id']));	

	$docs = array();
	
	$doc = new Apache_Solr_Document();
	$doc->id = $item['id'];
	foreach ($elementTexts as $elementText){
		$titleCount = 0;
		$fieldName = $elementText['element_id'] . '_s';
		$doc->setMultiValue($fieldName, $elementText['text']);
		//store Dublin Core titles as separate fields
		if ($elementText['element_id'] == 50){
			$doc->setMultiValue('title', $elementText['text']);
		}	
	}
	$docs[] = $doc;
	try {
    	$solr->addDocuments($docs);
		$solr->commit();
		$solr->optimize();
	}
	catch ( Exception $err ) {
		echo $err->getMessage();
	}
}

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
    if (get_acl()->checkUserPermission('SolrSearch_Config', 'index')) {
        $tabs['Solr Config'] = uri('solr-search/config/');        
    }
    return $tabs;
}
	
function solr_search_define_acl($acl)
{
    $acl->loadResourceList(array('SolrSearch_Config' => array('index', 'status')));
}

function solr_search_admin_header($request)
{
	if ($request->getModuleName() == 'solr-search') {
		echo '<link rel="stylesheet" href="' . html_escape(css('solr_search_main')) . '" />';
		//echo js('generic_xml_import_main');
    }
}

function solr_search_public_header($request)
{
	if ($request->getModuleName() == 'solr-search') {
		echo '<link rel="stylesheet" href="' . html_escape(css('solr_search_public')) . '" />';
		//echo js('generic_xml_import_main');
    }
}

//select fields to display in Solr search results
function solr_search_config_form()
{
	$form = solr_search_display_fields();
	?>
	<div class="field">
		<h3>Select displayable fields</h3>
		<p class="explanation">Use this form to select fields to be displayed in Solr search results.</p>
		<? echo $form; ?>
	</div>
<?php
}

//post displable fields to index
function solr_search_config(){
	$form = solr_search_display_fields();
    if ($form->isValid($_POST)) {    
    	//get posted values		
		$uploadedData = $form->getValues();
		
		//cycle through each checkbox
		foreach ($uploadedData as $k => $v){
			if ($k != 'submit'){
				$split = explode('_', $k);
				$data = array('id'=>$split[0], 'is_displayed'=>$v);
				$db = get_db();
				$db->insert('solr_search_facets', $data); 
			}		
		}
    }
}

/*********
 * Displayable element form
 *********/
function solr_search_display_fields(){
    require "Zend/Form/Element.php";
    $form = new Zend_Form();
	//$form->setAction('solr-search/display/update');    	
    $form->setMethod('post');
    $form->setAttrib('enctype', 'multipart/form-data');
    
    //set form as a table
    $form->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'table')),'Form',));	    	
    
    $db = get_db();
    $elements = $db->getTable('SolrSearch_Facet')->findAll();
    
    foreach ($elements as $element){	    		
    	$elementSetName = $db->getTable('ElementSet')->find($element['element_set_id'])->name;
    	$displayedFieldName = new Zend_Form_Element_Checkbox($element['id'] . '_facetCheckbox');
    	$displayedFieldName->setLabel($elementSetName . ': ' . $element['name']);
    	if ($element['is_displayed'] == 1){
    		$displayedFieldName->setCheckedValue(true)
    			->setValue(true);
    	}
    	
    	//set each element as a table row
    	$displayedFieldName->setDecorators(array('ViewHelper',
		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
		array('Label', array('tag' => 'td')), array(array('row' => 'HtmlTag'), array('tag' => 'tr')),));	
    	$form->addElement($displayedFieldName);
    }    	
    
    return $form;
}

/*********
 * Custom Theme Helpers
 *********/

function solr_search_form($buttonText = "Search", $formProperties=array('id'=>'simple-search')) 
{ 
	$uri = WEB_ROOT . '/solr-search/results/';
    $formProperties['action'] = $uri;
    $formProperties['method'] = 'get';
    $html  = '<form ' . _tag_attributes($formProperties) . '>' . "\n";
    $html .= '<fieldset>' . "\n\n";
    $html .= __v()->formText('q', '', array('name'=>'textinput','class'=>'textinput'));
    $html .= __v()->formSubmit('submit_search', $buttonText);
    $html .= '</fieldset>' . "\n\n";
    $html .= '</form>';
    return $html;
}

function solr_search_element_lookup($field){
		$fieldarray = explode('_', $field);
		$fieldId = $fieldarray[0];
		$db = get_db();
		$element = $db->getTable('Element')->find($fieldId);
		return $element['name'];
}

function solr_search_result_link($doc){
	if (is_array($doc->title)){
		if ($doc->title[0] == ''){
			$title = '[Untitled]';
		} else{
			$title = $doc->title[0];
		}
	} else {
		if ($doc->title == ''){
			$title = '[Untitled]';
		} else{
			$title = $doc->title;
		}
	}

	
	$uri = html_escape(WEB_ROOT) . '/items/show/';
	return '<a href="' . $uri . $doc->id .'">' . $title . '</a>';
}

function solr_search_facet_link($facet,$label,$count){
	$uri = html_escape(WEB_ROOT) . '/solr-search/results/';
	if(strstr($_REQUEST['q'], $facet . ':"' . $label . '"'))
	{
		$queryParams = explode(' AND ', $_REQUEST['q']);
		$removeFacetLink = "<a href='" . $uri . '?q=';
		foreach ($queryParams as $key => $queryParam){
			if($queryParam != $facet . ':"' . $label . '"'){
				if ($key > 0){
					$removeFacetLink .= ' AND ';
				}
				$removeFacetLink .= $queryParam;
			}
		}
		$removeFacetLink .= "'>[X]</a>";
	
		$html .= '<div class="fn"><b>' . $label . '</b></div>';
		$html .= '<div class="fc">' . $removeFacetLink . '</div>';
		return $html;
	} else{
		$html .= "<div class='fn'><a href='" . $uri . '?q=' . html_escape($_REQUEST['q']) . ' AND ' . $facet . ':&#x022;' . $label ."&#x022;'>" . $label . '</a></div>';
		$html .= '<div class="fc">' . $count . '</div>';
		return $html;
	}

}
