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

        try {
            $solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);
            $solr->deleteByQuery('*:*');
            $solr->commit();
            $solr->optimize();
        } catch (Exception $err ) {
            echo $err->getMessage();
        }

        self::_deleteOptions();
    }

    public function beforeDeleteItem($item)
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

    public function afterSaveItem($item)
    {
        $solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);

        if($item['public'] == true){
            $elementTexts = $this->_db
                ->getTable('ElementText')
                ->findBySql('record_id = ?', array($item['id']));

            $docs = array();
            $doc = new Apaache_Solr_Document();
            $doc->id = $item['id'];
            foreach ($elementTexts as $elementText) {
                $titleCount = 0;
                $fieldName = $elementText['element_id'] . '_s';
                $doc->setMultiValue($fieldName, $elementText['text']);

                // Store dc:titles as separate fields.
                if ($elementText['element_id'] == 50) {
                    $doc->setMultiValue('title', $elementText['text']);
                }
            }

            // Tags
            foreach ($item->Tags as $key => $tag) {
                $doc->setMultiValue('tag', $tag);
            }

            // Collections
            if ($item['collection_id'] > 0) {
                $collectionName = $this->_db
                    ->getTable('Collection')
                    ->find($item['collection_id'])
                    ->name;
                $doc->collection = $collectionName;
            }

            // Item Type
            if ($item['item_type_id'] > 0) {
                $itemType = $this->_db
                    ->getTable('ItemType')
                    ->find($item['item_type_id'])
                    ->name;
                $doc->itemtype = $itemType;
            }

            // Images or XML files
            $files = $item->Files;
            foreach ($files as $file) {
                $mimeType = $file->mime_browser;
                if ($file['has_derivative_image'] == 1) {
                    $doc->setMultiValue('image', $file['id']);
                }

                if ($mimeType == 'application/xml' || $mimeType == 'text/xml') {
                    $teiFile = $file->getPath('archive');
                    $this->_indexXml($teiFile, $doc);
                }
            }

            // FedoraConnector XML
            if (function_exists('fedora_connector_installed')) {
                $dataStreams = $this->_db
                    ->getTable('FedoraConnector_Datastream')
                    ->findBySql('mime_type=? AND item_id=?', array('text/xml', $item->id));

                foreach ($dataStreams as $ds) {
                    $fedoraFile = fedora_connector_content_url($ds);
                    $this->_indexXml($fedoraFile, $doc);
                }
            }

            $docs[] = $doc;
            try {
                $solr->addDocuments($docs);
                $solr->commit();
                $solr->optimize();
            } catch (Exception $err) {
                echo $err->getMessage();
            }
        } else {
            // If the item's no longer public, remove it from the index.
            try {
                $solr->deleteByQuery('id:' . $item['id']);
                $solr->commit();
                $solr->optimize();
            } catch (Exception $err) {
                echo $err->getMessage();
            }
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
            queue_css('solr_search_main');
        }
    }

    public function publicThemeHeader()
    {
        if($request->getModuleName() == 'solr-search') {
            queue_css('solr_search_public');
        }
    }

    public function configForm()
    {
        $fields = $this->_makeConfigFields();
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
        $form = $this->_makeConfigForm();

        if ($form->isValid($_POST)) {
            $options = $form->getValues();

            foreach ($options as $option => $value) {
                set_option($option, $value);
            }

            ProcessDispatcher::startProcess('SolrSearch_IndexAll', null, $args);
        } else {
            // TODO: Need to fix this with the rest of the error message
            // handling.
            echo '<div class="errors">';
            var_dump($form->getMessages());
            echo '</div>';
        }
    }

    public function adminNavigationMain($tabs)
    {
        if (get_acl()->checkUserPermission('SolrSearch_Config', 'index')) {
            $tabs['Solr Index'] = uri('solr-search/config/');
        }
        return $tabs;
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
        $sql = <<<SQL
            INSERT INTO `{$this->_db->prefix}solr_search_facets`
                (name, is_facet, is_displayed, is_sortable)
                VALUES (?, ?, ?, ?);
SQL;
        $stmt = $this->_db->prepare($sql);

        $stmt->execute(array('image',      1, 1, 1));
        $stmt->execute(array('tag',        1, 1, 1));
        $stmt->execute(array('collection', 1, 1, 1));
        $stmt->execute(array('itemtype',   1, 1, 1));

        $sql = <<<SQL
            INSERT INTO `{$this->_db->prefix}solr_search_facets`
                (element_id, name, element_set_id)
                VALUES (?, ?, ?);
SQL;
        $stmt = $this->_db->prepare($sql);
        $elements = $this->_db->getTable('Element')->findAll();

        foreach ($elements as $element) {
            $stmt->execute(array(
                $element['id'], $element['name'], $element['element_set_id']
            ));
        }
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

    protected function _indexXml($filename, $solrDoc) {
        $xml = new DomDocument();
        $xml->load($filename);
        $xpath = new DOMXPath($xml);

        $nodes = $xpath->query('//text()');
        foreach ($nodes as $node) {
            $value = preg_replace('/\s\s+/', ' ', trim($node->nodeValue));
            if ($value != ' ' && $value != '') {
                $solrDoc->setMultiValue('fulltext', $value);
            }
        }
    }

    protected function _makeConfigForm() {
        $form = new Zend_Form();
        $this->_makeConfigFields($form);
        return $form;
    }

    protected function _makeConfigFields($form) {
        $fields = array();

        $fields[] = $this->_makeOptionField(
            $form, 'solr_search_server', 'Server Host:', true
        );
        $fields[] = $this->_makeOptionField(
            $form, 'solr_search_port', 'Server Port:', true
        )
            ->addValidator(new Zend_Validate_Digits());
        $fields[] = $this->_makeOptionField(
            $form, 'solr_search_core', 'Solr Core Name:', true
        )
            ->addValidator('regex', true, array('/\/.*\//i'));

        $fields[] = $this->_makeOptionField(
            $form, 'solr_search_rows', 'Results Per Page:', true
        )
            ->addValidator(new Zend_Validate_Digits())
            ->addErrorMessage('Results count must be numeric');

        $fields[] = $this->_makeOptionField(
            $form, 'solr_search_facet_sort', 'Default Sort Order:', false,
            'Zend_Form_Element_Select'
        )
            ->addMultiOption('index', 'Alphabetical')
            ->addMultiOption('count', 'Occurrences');

        $fields[] = $this->_makeOptionField(
            $form, 'solr_search_facet_limit', 'Maximum Facet Count:', true
        )
            ->addValidator(new Zend_Validate_Digits());

        return $fields;
    }

    protected function _makeOptionField(
        $form, $name, $label, $required, $cls='Zend_Form_Element_Text'
    ) {
        $field = new $cls($name, array(
            'label'    => $label,
            'value'    => get_option($name),
            'required' => $required
        ));

        if ($form != null) {
            $form->addElement($field);
        }

        return $field;
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
