<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * SolrSearch Omeka plugin
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package omeka
 * @subpackage SolrSearch
 * @author Scholars' Lab
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @copyright 2010 The Board and Visitors of the University of Virginia
 * @link https://github.com/scholarslab/SolrSearch/
 *
 * PHP version 5
 */

class SolrPlugin
{
  // {{{ hooks
  private static $_hooks = array(
    'install',
    'uninstall',
    'before_delete_item',
    'after_save_item',
    'define_routes',
    'define_acl',
    'admin_theme_header',
    'public_theme_header',
    'config_form',
    'config'
  );
  //}}}

  //{{{ filters
  private static $_filters = array(
    'admin_navigation_main'
  );
  //}}}

  public function __construct()
  {
    $this->_db = get_db();
    self::addHooksAndFilters();
  }

  public function addHooksAndFilters()
  {
    foreach(self::$_hooks as $hookName) {
      $functionName = Inflector::variablize($hookName);
      add_plugin_hook($hookName, array($this, $functionName));
    }

    foreach(self::$_filters as $filterName) {
      $functionName = Inflector::variablize($hookName);
      add_filter($filterName, array($this, $functionName));
    }
  }

  public function install()
  {
    self::_createSolrTable();
    self::_addFacetMappings();
    self::_setOptions();
  }


  public function uninstall()
  {
    $sql = "DROP TABLE IF EXISTS `{$this->_db->prefix}solr_search_facets`";
    $db->query($sql);

    $solr - new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);

    try {
      $solr->deleteByQuery('*:*');
      $solr->commit();
      $solr->optimize();
    } catch (Exception $err ) {
      echo $err->getMessage();
    }

    self::_deleteOptions();
  }

  public function beforeDeleteItem()
  {

  }

  public function afterSaveItem()
  {
    $solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);

    if($item['public'] == true){
      $elementTexts = $this->_db->getTable('ElementText')->findBySql(
        'record_id = ?', array($item['id'])
      );
    }
  }

  public function defineRoutes()
  {

  }

  public function defineAcl()
  {

  }

  public function adminThemeHeader()
  {
    if($request->getModuleName() === 'solr-search') {
      queue_css('solr_search_main');
    }
  }

  public function publicThemeHeader()
  {
    if($request->getModuleName() === 'solr-search') {
      queue_css('solr_search_public');
    }
  }

  public function configForm()
  {

  }

  public function config()
  {

  }

    /**
     * Intercept simple search request and redirect to SolrSearch.
     *
     * @param string $uri The default simple search uri.
     *
     * @return void.
     */
    public function simpleSearchDefaultUri($uri)
    {
        return $uri;
    }

  // {{{protected

  /**
   * Populates the facet table with human readable mappings of Omeka Element ids
   *
   * There are special cases for sorting <tt>tags</tt>, 
   * <tt>collection</tt>, and <tt>itemType</tt>
   *
   */
  protected function _addFacetMappings()
  {

    //TODO: refactor as a prepared statement
    //$data[] = array(
    $this->_db->insert('solr_search_facets', array(
      'name' => 'image',
      'is_displayed' => 1,
      'is_facet' => 1,
      'is_sortable' => 1
    );

     //$data[] = array(
    $this->_db->insert('solr_search_facets', array(
      'name' => 'tag',
      'is_displayed' => 1,
      'is_facet' => 1,
      'is_sortable' => 1
    );

    //$data[] = array(
    $this->_db->insert('solr_search_facets', array(
      'name' => 'tag',
      'is_displayed' => 1,
      'is_facet' => 1,
      'is_sortable' => 1
    );
  
    $elements = $this->_db->getTable('Element')->findAll();

    foreach ($elements as $element){
		  $data[] = array(
		    'element_id'     => $element['id'],
        'name'           => $element['name'],
        'element_set_id' => $element['element_set_id']
      );

		  $this->_db->insert('solr_search_facets', $data);
    }	

    //    $values = implode(',' array_fill(0, count($data), '(?)'));
    //    $stmt = $table->getAdapter()->prepare('INSERT INTO...');
    //    $stmt->execute($data);

  }

  protected function _setOptions()
  {
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

  protected function _deleteOptions()
  {
    delete_option('solr_search_server', 'localhost');
	  delete_option('solr_search_port', '8080');
	  delete_option('solr_search_core', '/solr/');
	  delete_option('solr_search_rows', '10');
	  delete_option('solr_search_facet_limit', '25');
	  delete_option('solr_search_hl', 'false');
	  delete_option('solr_search_snippets', '1');
	  delete_option('solr_search_fragsize', '100');
	  delete_option('solr_search_facet_sort', 'count');
  }


  protected function _createSolrTable()
  {
    $sql = <<<SQL
      CREATE TABLE IF NOT EXISTS `{$db->prefix}solr_search_facets` (
        `id` int(10) unsigned NOT NULL auto_increment,
		    `element_id` int(10) unsigned,
		    `name` tinytext collate utf8_unicode_ci NOT NULL,	      
		    `element_set_id` int(10) unsigned,
		    `is_facet` tinyint unsigned DEFAULT 0,
		    `is_displayed` tinyint unsigned DEFAULT 0,		
		    `is_sortable` tinyint unsigned DEFAULT 0,
        PRIMARY KEY  (`id`)
       ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;

    $this->_db->exec($sql);

  }
  //}}}

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
