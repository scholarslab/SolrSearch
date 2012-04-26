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

class SolrSearchPlugin
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
        'admin_navigation_main',
        'simple_search_default_uri'
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
            $functionName = Inflector::variablize($filterName);
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
        $this->_db->query($sql);

        $solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);
        $solr->deleteByQuery('*:*');
        $solr->commit();
        $solr->optimize();

        self::_deleteOptions();
    }

    public function beforeDeleteItem($item)
    {
        $solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);
        $solr->deleteByQuery('id:' . $item['id']);
        $solr->commit();
        $solr->optimize();
    }

    public function afterSaveItem($item)
    {
        $solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);

        if ($item['public'] == true) {
            $docs = array();
            $doc = SolrSearch_IndexHelpers::itemToDocument($this->_db, $item);
            $docs[] = $doc;

            $solr->addDocuments($docs);
            $solr->commit();
            $solr->optimize();
        } else {
            // If the item's no longer public, remove it from the index.
            $solr->deleteByQuery('id:' . $item['id']);
            $solr->commit();
            $solr->optimize();
        }
    }

    public function defineRoutes($router)
    {
        $searchResultsRoute = new Zend_Controller_Router_Route(
            'results', array(
                'controller' => 'search',
                'action'     => 'results',
                'module'     => 'solr-search'
            ));
        $router->addRoute('solr_search_results_route', $searchResultsRoute);
    }

    public function defineAcl($acl)
    {
        $acl->loadResourceList(array(
            'SolrSearch_Config' => array('index', 'status')
        ));
    }

    public function adminThemeHeader($request)
    {
        $module = $request->getModuleName();
        if ($module == 'solr-search' || $module == 'default') {
            queue_css('solr_search');
        }
    }

    public function publicThemeHeader()
    {
        queue_css('solr_search');
        $js = 'solrsearch-' . SOLR_SEARCH_PLUGIN_VERSION . '-min';
        queue_js($js);
    }

    public function configForm()
    {
        $fields = SolrSearch_ViewHelpers::makeConfigFields();
        $buffer = array();

        $buffer[] = '<div class="field solrsearch config"><h3>Solr Options</h3>';
        foreach ($fields as $field) {
            $buffer[] = $field->render();
        }
        $buffer[] = '</div>';

        echo join($buffer);
    }

    public function config()
    {
        $form = SolrSearch_ViewHelpers::makeConfigForm();

        if ($form->isValid($_POST)) {
            $options = $form->getValues();

            if (!SolrSearch_IndexHelpers::pingSolrServer($options)) {
                throw new Omeka_Validator_Exception(
                    "Invalid Solr server host, port, or core."
                );
            }

            foreach ($options as $option => $value) {
                set_option($option, $value);
            }

            $this->_flashSuccess("Config updated.");

            ProcessDispatcher::startProcess('SolrSearch_IndexAll', null, $args);
        } else {
            $output = '';
            foreach ($form->getMessages() as $code => $msgs) {
                foreach ($msgs as $msg) {
                    $output .= "$msg ($code)\n";
                }
            }
            throw new Omeka_Validator_Exception($output);
        }
    }

    public function adminNavigationMain($tabs)
    {
        if (get_acl()->checkUserPermission('SolrSearch_Config', 'index')) {
            $tabs['Solr Index'] = uri('solr-search/config/');
        }
        return $tabs;
    }

    public function simpleSearchDefaultUri($uri)
    {
        $uri = uri('solr-search/results/interceptor');
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
        $elements = $this->_db->getTable('Element')->findAll();
        $sql = <<<SQL
            INSERT INTO `{$this->_db->prefix}solr_search_facets`
                (element_id, name, element_set_id, is_facet, is_displayed, is_sortable)
                VALUES (?, ?, ?, ?, ?, ?);
SQL;
        $stmt = $this->_db->prepare($sql);

        $stmt->execute(array(null, 'Image',      null, 1, 1, 1));
        $stmt->execute(array(null, 'Tag',        null, 1, 1, 1));
        $stmt->execute(array(null, 'Collection', null, 1, 1, 1));
        $stmt->execute(array(null, 'Itemtype',   null, 1, 1, 1));

        foreach ($elements as $element) {
            $stmt->execute(array(
                $element['id'], $element['name'], $element['element_set_id'], 0, 0, 0
            ));
        }
    }

    protected function _setOptions()
    {
        set_option('solr_search_server', 'localhost');
        set_option('solr_search_port', '8080');
        set_option('solr_search_core', '/solr/');
        set_option('solr_search_rows', '');
        set_option('solr_search_facet_limit', '25');
        set_option('solr_search_hl', 'true');
        set_option('solr_search_snippets', '1');
        set_option('solr_search_fragsize', '250');
        set_option('solr_search_facet_sort', 'count');
    }

    protected function _deleteOptions()
    {
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


    protected function _createSolrTable()
    {
        $sql = <<<SQL
      CREATE TABLE IF NOT EXISTS `{$this->_db->prefix}solr_search_facets` (
        `id` int(10) unsigned NOT NULL auto_increment,
            `element_id` int(10) unsigned,
            `name` tinytext collate utf8_unicode_ci NOT NULL,
            `element_set_id` int(10) unsigned,
            `is_facet` tinyint unsigned DEFAULT 0,
            `is_displayed` tinyint unsigned DEFAULT 0,
            `is_sortable` tinyint unsigned DEFAULT 0,
        PRIMARY KEY  (`id`)
       ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;

        $this->_db->exec($sql);

    }

    /**
     * This sets a flash error message.
     *
     * @param string $msg The message to flash.
     *
     * @return void
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    protected function _flashError($msg)
    {
        $flash = new Omeka_Controller_Flash;
        $flash->setFlash(Omeka_Controller_Flash::GENERAL_ERROR, 
                         $msg, 
                         Omeka_Controller_Flash::DISPLAY_NEXT);
    }

    protected function _flashSuccess($msg)
    {
        $flash = new Omeka_Controller_Flash;
        $flash->setFlash(Omeka_Controller_Flash::SUCCESS, 
                         $msg, 
                         Omeka_Controller_Flash::DISPLAY_NEXT);
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
