<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchPlugin extends Omeka_Plugin_AbstractPlugin
{

    // {{{ hooks
    protected $_hooks = array(
        'install',
        'uninstall',
        'initialize',
        'after_save_record',
        'after_save_item',
        'before_delete_record',
        'before_delete_item',
        'define_routes',
        'define_acl',
        'admin_head',
        'public_head',
        'config_form',
        'config'
    );
    //}}}

    //{{{ filters
    protected $_filters = array(
        'admin_navigation_main',
        'search_form_default_action'
    );
    //}}}

    function __construct()
    {
        parent::__construct();
        $this->setUp();
    }

    public function hookInstall()
    {
        self::_createSolrTable();
        self::_addFacetMappings();
        self::_setOptions();
    }

    public function hookUninstall()
    {
        $sql = "DROP TABLE IF EXISTS `{$this->_db->prefix}solr_search_facets`";
        $this->_db->query($sql);

        try {
            $solr = new Apache_Solr_Service(
                get_option('solr_search_server'),
                get_option('solr_search_port'),
                get_option('solr_search_core')
            );
            $solr->deleteByQuery('*:*');
            $solr->commit();
            $solr->optimize();
        } catch (Exception $e) {
        }

        self::_deleteOptions();
        self::_deleteAcl();
    }

    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');
    }

    public function hookAfterSaveRecord($args)
    {
        SolrSearch_Utils::ensureView();

        $record = $args['record'];
        $mgr = new SolrSearch_Addon_Manager($this->_db);
        $doc = $mgr->indexRecord($record);

        if (!is_null($doc)) {
            $solr = new Apache_Solr_Service(
                get_option('solr_search_server'),
                get_option('solr_search_port'),
                get_option('solr_search_core')
            );
            $solr->addDocuments(array($doc));
            $solr->commit();
            $solr->optimize();
        }
    }

    public function hookAfterSaveItem($args)
    {
        SolrSearch_Utils::ensureView();

        $item = $args['record'];
        $solr = new Apache_Solr_Service(
            get_option('solr_search_server'),
            get_option('solr_search_port'),
            get_option('solr_search_core')
        );

        if ($item['public'] == true) {
            $docs = array();
            $doc = SolrSearch_IndexHelpers::itemToDocument($this->_db, $item);
            $docs[] = $doc;

            $solr->addDocuments($docs);
            $solr->commit();
            $solr->optimize();
        } else {
            // If the item's no longer public, remove it from the index.
            $solr->deleteById('Item_' . $item['id']);
            $solr->commit();
            $solr->optimize();
        }
    }

    public function hookBeforeDeleteRecord($args)
    {
        $record = $args['record'];
        $mgr = new SolrSearch_Addon_Manager($this->_db);
        $id = $mgr->getId($record);

        if (!is_null($id)) {
            $solr = new Apache_Solr_Service(
                get_option('solr_search_server'),
                get_option('solr_search_port'),
                get_option('solr_search_core')
            );
            try {
                $solr->deleteById($id);
                $solr->commit();
                $solr->optimize();
            } catch (Exception $e) {}
        }
    }

    public function hookBeforeDeleteItem($args)
    {
        $item = $args['record'];
        $solr = new Apache_Solr_Service(
            get_option('solr_search_server'),
            get_option('solr_search_port'),
            get_option('solr_search_core')
        );

        try {
            $solr->deleteById('Item_' . $item['id']);
            $solr->commit();
            $solr->optimize();
        } catch (Exception $e) {}
    }

    public function hookDefineRoutes($args)
    {
        $router = $args['router'];
        $searchResultsRoute = new Zend_Controller_Router_Route(
            'results',
            array(
                'controller' => 'search',
                'action'     => 'results',
                'module'     => 'solr-search'
            )
        );

        $router->addRoute('solr_search_results_route', $searchResultsRoute);
    }

    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        if (!$acl->has('SolrSearch_Config')) {
            $acl->addResource('SolrSearch_Config');
            $acl->allow(null, 'SolrSearch_Config', array('index', 'status'));
        }
    }

    public function hookAdminHead($args)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();

        // Let's figure out where we are.
        $module     = $request->getModuleName();
        $controller = $request->getControllerName();
        $action     = $request->getActionName();

        // If we're on any page using the SolrSearch module.
        if ($module == 'solr-search') {
            queue_css_file('textinplace');
            queue_js_file('jquery.textinplace');
            queue_css_file('solr_search_main');
        }

        // If we're on the plugin config page.
        if ($controller == 'plugins' && $action == 'config') {
            queue_css_file('solr_search_main');
        }
    }

    public function hookPublicHead($args)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $module  = $request->getModuleName();
        if ($module == 'solr-search') {
            queue_css_file('solr_search');
            $js = 'SolrSearch-' . SOLR_SEARCH_PLUGIN_VERSION . '-min';
            queue_js_file($js);
        }
    }

    public function hookConfigForm()
    {
        $string = __('Solr Options');
        $fields = SolrSearch_ViewHelpers::makeConfigFields();
        $buffer = array();

        $buffer[] = "<div class='field solrsearch config'><h3>$string</h3>";
        foreach ($fields as $field) {
            $buffer[] = $field->render();
        }
        $buffer[] = '</div>';

        echo join($buffer);
    }

    public function hookConfig()
    {

        $form = SolrSearch_ViewHelpers::makeConfigForm();

        if ($form->isValid($_POST)) {
            $options = $form->getValues();

            if (!SolrSearch_IndexHelpers::pingSolrServer($options)) {
                throw new Omeka_Validate_Exception(
                    __("Cannot connect to Solr with the given configuration. Please check your server host, port, and core.")
                );
            }

            foreach ($options as $option => $value) {
                set_option($option, $value);
            }

            Omeka_Job_Process_Dispatcher::startProcess('SolrSearch_IndexAll');

        } else {
            $output = '';
            foreach ($form->getMessages() as $code => $msgs) {
                foreach ($msgs as $msg) {
                    $output .= "$msg ($code)\n";
                }
            }

            throw new Omeka_Validate_Exception($output);

        }
    }

    public function filterAdminNavigationMain($nav)
    {
        if (is_allowed('SolrSearch_Config', 'index')) {
            $nav[] = array(
                'label' => __('SolrSearch'),
                'uri' => url('solr-search/config/')
            );
        }
        return $nav;
    }

    /**
     * Overrides the default simple-search URI to automagically integrate in
     * to the theme; leaves admin section alone for default search.
     *
     * @param string $uri URI for Simple Search
     * @return string URI;
     */
    public function filterSearchFormDefaultAction($uri)
    {
        if (! is_admin_theme()) {
            $uri = url('solr-search/results/interceptor');
        }

        return $uri;
    }

    // {{{protected

    /**
     * Populates the facet table with human readable mappings of Omeka Element
     * ids.
     *
     * There are special cases for sorting <tt>tags</tt>,
     * <tt>collection</tt>, and <tt>itemType</tt>
     */
    protected function _addFacetMappings()
    {
        $dc = $this->_db->getTable('ElementSet')->findByName('Dublin Core');
        $defaults = array(
            'title'       => 1,
            'description' => 1
        );

        $elements = $this->_db->getTable('Element')->findAll();
        $sql = <<<SQL
            INSERT INTO `{$this->_db->prefix}solr_search_facets`
                (element_id, name, label, element_set_id, is_facet,
                is_displayed)
                VALUES (?, ?, ?, ?, ?, ?);
SQL;
        $stmt = $this->_db->prepare($sql);

        // $stmt->execute(array(null, 'Image',      null, 1, 1));
        $stmt->execute(array(null, 'tag',        __('Tag'),         null, 1, 1));
        $stmt->execute(array(null, 'collection', __('Collection'),  null, 1, 1));
        $stmt->execute(array(null, 'itemtype',   __('Item Type'),   null, 1, 1));
        $stmt->execute(array(null, 'resulttype', __('Result Type'), null, 1, 1));

        foreach ($elements as $element) {
            $v = 0;
            $eid  = $element['id'];
            $name = $element['name'];

            if ($element['element_set_id'] == $dc->id
                && array_key_exists(strtolower($name), $defaults)
            ) {

                $v = 1;
            }

            $stmt->execute(
                array(
                    $eid, "{$eid}_s",
                    $name,
                    $element['element_set_id'],
                    0,
                    $v
                )
            );
        }
    }

    protected function _setOptions()
    {
        set_option('solr_search_server', 'localhost');
        set_option('solr_search_port', '8080');
        set_option('solr_search_core', '/solr/omeka/');
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

    protected function _deleteAcl()
    {
        $acl = Zend_Registry::get('bootstrap')->getResource('Acl');;
        if (!$acl) {
            throw new RuntimeException(__('ACL not available'));
        }
        $acl->remove('SolrSearch_Config');
    }


    protected function _createSolrTable()
    {

        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS `{$this->_db->prefix}solr_search_facets` (

            `id`                int(10) unsigned NOT NULL auto_increment,
            `element_id`        int(10) unsigned,
            `name`              tinytext collate utf8_unicode_ci NOT NULL,
            `label`             tinytext collate utf8_unicode_ci NOT NULL,
            `element_set_id`    int(10) unsigned,
            `is_facet`          tinyint unsigned DEFAULT 0,
            `is_displayed`      tinyint unsigned DEFAULT 0,

            PRIMARY KEY (`id`)

        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;

        $this->_db->query($sql);

    }

    //}}}

}
