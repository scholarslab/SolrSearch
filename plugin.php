<?php
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
?>

<?php
// Define Omeka constants

define('SOLR_SEARCH_PLUGIN_VERSION', get_plugin_ini('SolrSearch', 'version'));
define('SOLR_SERVER', get_option('solr_search_server'));
define('SOLR_PORT', get_option('solr_search_port'));
define('SOLR_CORE', get_option('solr_search_core'));
define('SOLR_ROWS', get_option('solr_search_rows'));
define('SOLR_FACET_LIMIT', get_option('solr_search_facet_limit'));

// TODO: is this necessary?
define('DEBUG_STATUS', 'debug'); 

require_once 'lib/Document.php';
require_once 'lib/Response.php';
require_once 'lib/Service.php';

// Plugin hooks
add_plugin_hook('install', 'solr_search_install');
add_plugin_hook('uninstall', 'solr_search_uninstall');
add_plugin_hook('before_delete_item', 'solr_search_before_delete_item');
add_plugin_hook('after_save_item', 'solr_search_after_save_item');
add_plugin_hook('define_routes', 'solr_search_define_routes');
add_plugin_hook('define_acl', 'solr_search_define_acl');
add_plugin_hook('admin_theme_header', 'solr_search_admin_header');
add_plugin_hook('public_theme_header', 'solr_search_public_header');
add_plugin_hook('config_form', 'solr_search_config_form');
add_plugin_hook('config', 'solr_search_config');

// Filters for the plugin
add_filter('admin_navigation_main', 'solr_search_admin_navigation');

$logFile = LOGS_DIR . DIRECTORY_SEPARATOR . 'solrsearch.log';
$writer = new Zend_Log_Writer_Stream($logFile);
$logger = new Zend_Log($writer);

$logger->info('Starting the plugin...');

/**
 * Set up the database to hold information for Solr
 * 
 */
function solr_search_install()
{
    $logger->info('Installing plugin...');
    
    $db = get_db();
    
    // create table for facet mapping
    $db->exec(
        "CREATE TABLE IF NOT EXISTS `{$db->prefix}solr_search_facets` (
        `id` int(10) unsigned NOT NULL auto_increment,
        `element_id` int(10) unsigned,
        `name` tinytext collate utf8_unicode_ci NOT NULL,
        `element_set_id` int(10) unsigned,
        `is_facet` tinyint unsigned,
        `is_displayed` tinyint unsigned,	
        `is_sortable` tinyint unsigned,
        PRIMARY KEY  (`id`),
        INDEX idx_solr_element_id (`element_id`),
        INDEX idx_solr_elelent_set_id (`element_set_id`),
        INDEX idx_solr_is_facet (`is_facet`),
        INDEX idx_solr_is_displayed (`is_displayed`),
        INDEX idx_solr_is_sortable (`is_sortable`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"
    );

    $elements = $db->getTable('Element')->findAll();
    
    // add all element names to facet table for selection
    foreach ($elements as $element) {
        $data = array(	
            'element_id' => $element['id'],
            'name' => $element['name'],
            'element_set_id' => $element['element_set_id'],
            'is_facet' => 0,
            'is_displayed' => 0,
            'is_sortable'=>0
            );
        
        $db->insert('solr_search_facets', $data);
    }
    
    // add extra convenience fields for the index
    $fields = array('tag', 'collection', 'itemtype', 'image');
    
    solr_search_add_defaults($fields);

    set_default_options(); // set default options
    
    //add public items to Solr index - moved to config form submission
    //ProcessDispatcher::startProcess('SolrSearch_IndexAll', null, $args);
}



/**
 * Adds default values to the database 
 * 
 * <code>
 * <?php 
 *   $default_fields = array('image', 'tag');
 *   solr_search_add_defaults($default_fields);
 * ?>
 * </code>
 * 
 * @param String $fields list of fields to add default values for
 */
function solr_search_add_defaults($fields)
{
    $db = get_db();
    
    $logger->info(var_dump($fields));
    
    // iterate over each value in the list and add a     
    foreach (fields as $field) {
        $db->insert('solr_search_facets',
            array(
                'name' => $field,
                'is_facet' => 0,
                'is_displayed' => 0,
                'is_sortable' => 0
            )
        );
    }
}


/**
 * Set default options for the plugin
 */
function set_default_options()
{
    //set solr options
    set_option('solr_search_server', 'localhost');
    set_option('solr_search_port', '8080');
    set_option('solr_search_core', '/solr/');
    set_option('solr_search_rows', '10');
    set_option('solr_search_facet_limit', '25');
    set_option('solr_search_hl', 'false');
    set_option('solr_search_snippets', '1');
    set_option('solr_search_fragsize', '100');
    set_option('solr_search_facet_sort', 'count');
}

/**
 * Delete options set by the plugin
 */
function remove_options()
{
    //delete solr options
    delete_option('solr_search_server');
    delete_option('solr_search_port');
    delete_option('solr_search_core');
    delete_option('solr_search_rows');
    delete_option('solr_search_facet_limit');
    delete_option('solr_search_hl');
    delete_option('solr_search_snippets');
    delete_option('solr_search_fragsize');
    delete_option('solr_search_facet_sort');
}

/**
 * Uninstall the SolrSearch plugin
 */
function solr_search_uninstall()
{
    // Drop the table.
    $logger->info('Uninstalling');
    $db = get_db();
    $sql = "DROP TABLE IF EXISTS `{$db->prefix}solr_search_facets`";
    $db->query($sql);
    $logger->info('Dropped database...removing options');
    

    //remove_index();     // clean up the index	
    remove_options();   // clean up omeka options table
}

/**
 * Removes all items from the index
 * 
 * @return void
 * 
 * TODO: Is this a good idea?
 */
function remove_index()
{
    $solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);
    try {		
        $solr->deleteByQuery('*:*');
        $solr->commit();
        $solr->optimize(); 
    } catch ( Exception $err ) {
        echo 'Caught Exception in ' . get_class($err);
        echo $err->getMessage();
        // do nothing
        $logger->debug(var_dump($err));
    }
    
    return;
}



/**
 * Display the SolrSearch configuration form
 */
function solr_search_config_form()
{
    include 'config_form.php';
}


/**
 * Delete an item from the index
 * 
 * TODO: refactor out to appropriate model
 */
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

/**
 * Reindex an item
 * 
 * TODO: refactor out to appropriate model
 * TODO: CLEAN THIS UP!!!
 */
function solr_search_after_save_item($item)
{
    $solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);	
    //if item is public, save it to solr
    if ($item['public'] == '1') {		
        $db = get_db();
        $elementTexts = $db->getTable('ElementText')->findBySql('record_id = ?', array($item['id']));	
	
        $docs = array();
	
        $doc = new Apache_Solr_Document();
        $doc->id = $item['id'];
        
        foreach ($elementTexts as $elementText) {
            $titleCount = 0;
            $fieldName = $elementText['element_id'] . '_s';
            $doc->setMultiValue($fieldName, $elementText['text']);
           
            //store Dublin Core titles as separate fields
            if ($elementText['element_id'] == 50) {
                $doc->setMultiValue('title', $elementText['text']);
                
            }
        }
		
        //add tags			
        foreach($item->Tags as $key => $tag) {
            $doc->setMultiValue('tag', $tag);
        }
	
        //add collection
        if ($item['collection_id'] > 0) {
            $collectionName = $db->getTable('Collection')->find($item['collection_id'])->name;
            $doc->collection = $collectionName;
        }
		
        //add item type
        if ($item['item_type_id'] > 0) {
            $itemType = $db->getTable('ItemType')->find($item['item_type_id'])->name;
            $doc->itemtype = $itemType;
        }
		
        //add images or index XML files
        $files = $item->Files;
        
        foreach ($files as $file) {
            $mimeType = $file->mime_browser;
            
            if($file['has_derivative_image'] == 1) {
                $doc->setMultiValue('image', $file['id']);
            }
            
            if ($mimeType == 'application/xml' || $mimeType == 'text/xml') {
                
                $teiFile = $file->getPath('archive');
                
                $xmlDoc = new DomDocument;	
                $xmlDoc->load($teiFile);
                $xpath = new DOMXPath($xmlDoc);
                $nodes = $xpath->query('//text()');
                
                foreach ($nodes as $node) {
                    
                    $value = preg_replace(
                        '/\s\s+/', 
                        ' ', 
                        trim($node->nodeValue)
                    );
                    
                    if ($value != ' ' && $value != '') {
                        $doc->setMultiValue('fulltext', $value);
                    }
                    
                }
            }
        }
		
        //if FedoraConnector is installed, index fulltext of XML
        if (function_exists('fedora_connector_installed')) {
            $datastreams = $db->getTable('FedoraConnector_Datastream')->findBySql('mime_type = ? AND item_id = ?', array('text/xml', $item->id));

            foreach ($datastreams as $datastream) {
                $teiFile = fedora_connector_content_url($datastream);
                $fedoraDoc = new DomDocument;
                $fedoraDoc->load($teiFile);
                $xpath = new DOMXPath($fedoraDoc);
                $nodes = $xpath->query('//text()');
                
                foreach ($nodes as $node) {
                    $value = preg_replace('/\s\s+/', ' ', trim($node->nodeValue));
                    
                    if ($value != ' ' && $value != '') {
                        $doc->setMultiValue('fulltext', $value);
                    }
                    
                }
            }
        }
		
        $docs[] = $doc;
        
        try {
            $solr->addDocuments($docs);
            $solr->commit();
            $solr->optimize();
        } catch ( Exception $err ) {
            echo $err->getMessage(); // TODO: send to logger
        }
	
        
    } else {
        //if item is no longer set as public, remove the item from index
        try {		
            $solr->deleteByQuery('id:' . $item['id']);
            $solr->commit();
            $solr->optimize(); 
        } catch ( Exception $err ) {
            echo $err->getMessage();// TODO: send to logger
        }
        
    }
}

/**
 *
 * @param type $child 
 */
function xml_dom_iteration($child)
{
    $doc->setMultiValue('fulltext', $child);
    
    foreach ($child->children() as $child) {
        xml_dom_iteration($child);	
    }
}

/**
 * Define plugin routes.
 * 
 * @param Zend_Controller_Router_Rewrite
 */
function solr_search_define_routes($router)
{
    $searchResultsRoute = new Zend_Controller_Router_Route('results',
            array(
                'controller' => 'search', 
                'action'     => 'results', 
                'module'     => 'solr-search'
            )
    );

    $router->addRoute('solr_search_results_route', $searchResultsRoute);
}

/**
 * Attach navigation tabs
 * 
 * @param type $tabs
 * @return type 
 */
function solr_search_admin_navigation($tabs)
{
    if (get_acl()->checkUserPermission('SolrSearch_Config', 'index')) {
        $tabs['Configure Solr'] = uri('solr-search/config/');        
    }
    return $tabs;
}

/**
 * Define the ACLs for the plugins
 * 
 * @param type $acl 
 */
function solr_search_define_acl($acl)
{
    $acl->loadResourceList(array('SolrSearch_Config' => array('index', 'status')));
}

/**
 * Attach the solr_search_main css to the CSS view
 * 
 * @param type $request 
 */
function solr_search_admin_header($request)
{
    if ($request->getModuleName() == 'solr-search') {
        echo '<link rel="stylesheet" href="' . html_escape(css('solr_search_main')) . '" />';
	//echo js('generic_xml_import_main');
    }
}

/**
 * Attach the solr plugin css to public views
 * 
 * @param type $request 
 */
function solr_search_public_header($request)
{
    if ($request->getModuleName() == 'solr-search') {
        $css_url = html_escape(css('solr_search_public'));
        echo '<link rel="stylesheet" href="' . $css_url . '" />';
        //echo js('generic_xml_import_main');
    }
}

/**
 * Post displayable fields to index
 * 
 * TODO: this goes in the admin controller...
 * 
 */
function solr_search_config(){
    $form = solr_search_options();
    
    if ($form->isValid($_POST)) {    
       //get posted values		
       $uploadedData = $form->getValues();
       
        //cycle through each checkbox
        foreach ($uploadedData as $k => $v) {
            if ($k != 'submit') {
                set_option($k, $v);
            }		
        }

        ProcessDispatcher::startProcess('SolrSearch_IndexAll', null, $args);
    }
}

/**
 * Displayable element form
 * 
 * TODO: Refactor as admin view
 * 
 * @return Zend_Form 
 */
function solr_search_options()
{
    require_once "Zend/Form/Element.php";
    
    $db = get_db();
    
    $form = new Zend_Form();	
    $form->setMethod('post');
    $form->setAttrib('enctype', 'multipart/form-data');	
    
    $solrServer = new Zend_Form_Element_Text ('solr_search_server');
    $solrServer->setLabel('Server:');
    $solrServer->setValue(get_option('solr_search_server'));
    $solrServer->setRequired('true');
    $form->addElement($solrServer);
    
    $solrPort = new Zend_Form_Element_Text ('solr_search_port');
    $solrPort->setLabel('Port:');
    $solrPort->setValue(get_option('solr_search_port'));
    $solrPort->setRequired('true');
    $solrPort->addValidator(new Zend_Validate_Digits());
    $form->addElement($solrPort);

    $solrCore = new Zend_Form_Element_Text ('solr_search_core');
    $solrCore->setLabel('Core:');
    $solrCore->setValue(get_option('solr_search_core'));
    $solrCore->setRequired('true');    
    $solrCore->addValidator('regex', true, array('/\/.*\//i'));
    $form->addElement($solrCore);
    
    $solrRows = new Zend_Form_Element_Text ('solr_search_rows');
    $solrRows->setLabel('Results Per Page:');
    $solrRows->setValue(get_option('solr_search_rows'));
    $solrRows->setRequired('true');
    $solrRows->addValidator(new Zend_Validate_Digits());
    $form->addElement($solrRows);
    
    $solrFacetSort = new Zend_Form_Element_Select ('solr_search_facet_sort');
    $solrFacetSort->setLabel('Facet Sort Order:');
    $solrFacetSort->addMultiOption('index', 'Alphabetical');
    $solrFacetSort->addMultiOption('count', 'Occurrences');    
    $solrFacetSort->setValue(get_option('solr_search_facet_sort'));
    $form->addElement($solrFacetSort);
    
    $solrFacetLimit = new Zend_Form_Element_Text ('solr_search_facet_limit');
    $solrFacetLimit->setLabel('Maximum Facet Constraint Count:');
    $solrFacetLimit->setValue(get_option('solr_search_facet_limit'));
    $solrFacetLimit->setRequired('true');
    $solrFacetLimit->addValidator(new Zend_Validate_Digits());
    $form->addElement($solrFacetLimit);
    
    return $form;
}

