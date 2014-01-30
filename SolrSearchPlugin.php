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
        'define_routes',
        'after_save_record',
        'after_save_item',
        'before_delete_record',
        'before_delete_item',
        'define_acl'
    );
    //}}}

    //{{{ filters
    protected $_filters = array(
        'admin_navigation_main',
        'search_form_default_action'
    );
    //}}}

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

    }

    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');
    }

    public function hookDefineRoutes($args)
    {
        $args['router']->addConfig(new Zend_Config_Ini(
            SOLR_DIR.'/routes.ini'
        ));
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
            $doc = SolrSearch_Helpers_Index::itemToDocument($this->_db, $item);
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

    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        if (!$acl->has('SolrSearch_Config')) {
            $acl->addResource('SolrSearch_Config');
            $acl->allow(null, 'SolrSearch_Config', array('index', 'status'));
        }
    }

    public function filterAdminNavigationMain($nav)
    {
        if (is_allowed('SolrSearch_Config', 'index')) {
            $nav[] = array(
                'label' => __('Solr'), 'uri' => url('solr-search')
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
        if (!is_admin_theme()) {
            $uri = url('solr-search/results/interceptor');
        }

        return $uri;
    }

    // {{{protected

    /**
     * Populate the facets table.
     */
    protected function _addFacetMappings()
    {

        $tag = new SolrSearchFacet();
        $tag->name                  = 'tag';
        $tag->label                 = __('Tag');
        $tag->is_displayed          = 1;
        $tag->is_facet              = 1;
        $tag->save();

        $collection = new SolrSearchFacet();
        $collection->name           = 'collection';
        $collection->label          = __('Collection');
        $collection->is_displayed   = 1;
        $collection->is_facet       = 1;
        $collection->save();

        $itemType = new SolrSearchFacet();
        $itemType->name             = 'itemtype';
        $itemType->label            = __('Item Type');
        $itemType->is_displayed     = 1;
        $itemType->is_facet         = 1;
        $itemType->save();

        $resultType = new SolrSearchFacet();
        $resultType->name           = 'resulttype';
        $resultType->label          = __('Result Type');
        $resultType->is_displayed   = 1;
        $resultType->is_facet       = 1;
        $resultType->save();

        foreach ($this->_db->getTable('Element')->findAll() as $element) {

            $facet = new SolrSearchFacet($element);
            $facet->is_facet = 0;

            // By default, make "Title" and "Decription" elements searchable.

            $facet->is_displayed =
                in_array($element->name, array('Title', 'Description')) ?
                1 : 0;

            $facet->save();

        }

    }

    protected function _setOptions()
    {
        set_option('solr_search_server', 'localhost');
        set_option('solr_search_port', '8080');
        set_option('solr_search_core', '/solr/collection1/');
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
        $this->_db->query(<<<SQL

        CREATE TABLE IF NOT EXISTS {$this->_db->prefix}solr_search_facets (

            id              int(10) unsigned NOT NULL auto_increment,
            element_id      int(10) unsigned,
            name            tinytext collate utf8_unicode_ci NOT NULL,
            label           tinytext collate utf8_unicode_ci NOT NULL,
            is_displayed    tinyint unsigned DEFAULT 0,
            is_facet        tinyint unsigned DEFAULT 0,

            PRIMARY KEY (id)

        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

SQL
);
    }

    //}}}

}
