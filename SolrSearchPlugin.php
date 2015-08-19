<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchPlugin extends Omeka_Plugin_AbstractPlugin
{


    protected $_hooks = array(
        'install',
        'uninstall',
        'upgrade',
        'initialize',
        'define_routes',
        'after_save_record',
        'after_save_item',
        'after_save_element',
        'before_delete_record',
        'before_delete_item',
        'before_delete_element'
    );


    protected $_filters = array(
        'admin_navigation_main',
        'search_form_default_action'
    );


    /**
     * Create the database tables, install the starting facets, and set the
     * default options.
     */
    public function hookInstall()
    {
        self::_createSolrTables();
        self::_installFacetMappings();
        self::_setOptions();
    }


    /**
     * Drop the database tables, flush the Solr index, and delete the options.
     */
    public function hookUninstall()
    {

        $this->_db->query(<<<SQL
        DROP TABLE IF EXISTS {$this->_db->prefix}solr_search_fields
SQL
        );
        $this->_db->query(<<<SQL
        DROP TABLE IF EXISTS {$this->_db->prefix}solr_search_excludes
SQL
        );

        try {
            $solr = SolrSearch_Helpers_Index::connect();
            $solr->deleteByQuery('*:*');
            $solr->commit();
            $solr->optimize();
        } catch (Exception $e) {}

        self::_clearOptions();

    }


    /**
     * If upgrading from 1.x, install the new schema.
     *
     * @param array $args Contains: `old_version` and `new_version`.
     */
    public function hookUpgrade($args)
    {
        self::_createSolrTables();
        if (version_compare($args['old_version'], '1.0.1', '<=')) {
            self::_installFacetMappings();
            self::_setOptions();
        }

        $fields = $this->_db->getTable('SolrSearchField');
        $featured = $fields->findBy(array('slug' => 'featured'));
        if (is_null($featured) || empty($featured)) {
            $this->_installGenericFacet('featured', __('Featured'));
        }

        if (version_compare($args['old_version'], '2.2.1', '<=')) {
            set_option('solr_search_hl_max_analyzed_chars', '51200');
        }
    }


    /**
     * Register the string translations.
     */
    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');
    }


    /**
     * Register the application routes.
     *
     * @param array $args With `router`.
     */
    public function hookDefineRoutes($args)
    {
        $args['router']->addConfig(new Zend_Config_Ini(
            SOLR_DIR.'/routes.ini'
        ));
    }


    /**
     * When a record is saved, try to extract and index a Solr document.
     *
     * @param array $args With `record`.
     */
    public function hookAfterSaveRecord($args)
    {

        SolrSearch_Utils::ensureView();

        $record = $args['record'];

        $excludes = get_db()->getTable('SolrSearchExclude');
        $collection = get_collection_for_item($record);
        if (!is_null($collection) && $excludes->isExcluded($collection)) {
            return;
        }

        // Try to extract a document for the record.
        $mgr = new SolrSearch_Addon_Manager($this->_db);
        $doc = $mgr->indexRecord($record);

        // Does the record have an add-on profile?
        if ($addon = $mgr->findAddonForRecord($record)) {

            // Connect to Solr.
            $solr = SolrSearch_Helpers_Index::connect();

            // If the record yields a Solr document, index it.
            if (!is_null($doc)) {
                $solr->addDocuments(array($doc));
                $solr->commit();
                $solr->optimize();
            }

            // If not, remove an existing document.
            else {
                try {
                    $solr->deleteById($mgr->getId($record));
                    $solr->commit();
                    $solr->optimize();
                } catch (Exception $e) {}
            }

        }

        // Reindex related records.
        $mgr->resaveRemoteParent($record);
        $mgr->resaveChildren($record);

    }


    /**
     * When an item is saved, index the record if the item is set public, and
     * clear an existing record if it is set private.
     *
     * @param array $args With `record`.
     */
    public function hookAfterSaveItem($args)
    {

        SolrSearch_Utils::ensureView();

        $item = $args['record'];

        $excludes = get_db()->getTable('SolrSearchExclude');
        $collection = get_collection_for_item($item);
        if (!is_null($collection) && $excludes->isExcluded($collection)) {
            return;
        }

        $solr = SolrSearch_Helpers_Index::connect();

        // Both public and private items will be indexed
        $doc = SolrSearch_Helpers_Index::itemToDocument($item);
        $solr->addDocuments(array($doc));
        $solr->commit();
        $solr->optimize();

    }


    /**
     * When a new element is added, register a facet mapping for it.
     *
     * @param array $args With `record` and `insert`.
     */
    public function hookAfterSaveElement($args)
    {
        if ($args['insert']) {
            $facet = new SolrSearchField($args['record']);
            $facet->save();
        }
    }


    /**
     * When a record is deleted, clear its Solr record.
     *
     * @param array $args With `record`.
     */
    public function hookBeforeDeleteRecord($args)
    {

        $record = $args['record'];
        $mgr = new SolrSearch_Addon_Manager($this->_db);
        $id = $mgr->getId($record);

        if (!is_null($id)) {
            $solr = SolrSearch_Helpers_Index::connect();
            try {
                $solr->deleteById($id);
                $solr->commit();
                $solr->optimize();
            } catch (Exception $e) {}
        }

    }


    /**
     * When an item is deleted, clear its Solr record.
     *
     * @param array $args With `record`.
     */
    public function hookBeforeDeleteItem($args)
    {

        $item = $args['record'];
        $solr = SolrSearch_Helpers_Index::connect();

        try {
            $solr->deleteById('Item_' . $item['id']);
            $solr->commit();
            $solr->optimize();
        } catch (Exception $e) {}

    }


    /**
     * When an element is deleted, remove its facet mapping.
     *
     * @param array $args With `record`.
     */
    public function hookBeforeDeleteElement($args)
    {
        $table = $this->_db->getTable('SolrSearchField');
        $facet = $table->findByElement($args['record']);

        if(!empty($facet)) {
            $facet->delete();
        }
    }


    /**
     * Add a link to the administrative navigation bar.
     *
     * @param string $nav The array of label/URI pairs.
     * @return array
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Solr Search'), 'uri' => url('solr-search/server')
        );
        return $nav;
    }


    /**
     * Override the default simple-search URI to automagically integrate into
     * the theme; leaves admin section alone for default search.
     *
     * @param string $uri URI for Simple Search.
     * @return string
     */
    public function filterSearchFormDefaultAction($uri)
    {
        if (!is_admin_theme()) $uri = url('solr-search/results/interceptor');
        return $uri;
    }


    /**
     * Install the facets table.
     */
    protected function _createSolrTables()
    {
        $this->_db->query(<<<SQL
        CREATE TABLE IF NOT EXISTS {$this->_db->prefix}solr_search_fields (
            id          int(10) unsigned NOT NULL auto_increment,
            element_id  int(10) unsigned,
            slug        tinytext collate utf8_unicode_ci NOT NULL,
            label       tinytext collate utf8_unicode_ci NOT NULL,
            is_indexed  tinyint unsigned DEFAULT 0,
            is_facet    tinyint unsigned DEFAULT 0,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );

        $this->_db->query(<<<SQL
        CREATE TABLE IF NOT EXISTS {$this->_db->prefix}solr_search_excludes (
            id            int(10) unsigned NOT NULL auto_increment,
            collection_id int(10) unsigned NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
    }


    /**
     * Install the default facet mappings.
     */
    protected function _installFacetMappings()
    {
        $this->_db
            ->getTable('SolrSearchField')
            ->installFacetMappings();
    }


    /**
     * Install the default facet mappings.
     *
     * @param string $slug The facet `slug`.
     * @param string $label The facet `label`.
     */
    protected function _installGenericFacet($slug, $label)
    {
        $this->_db
            ->getTable('SolrSearchField')
            ->installGenericFacet($slub, $label);
    }


    /**
     * Set the global options.
     */
    protected function _setOptions()
    {
        set_option('solr_search_host',          'localhost');
        set_option('solr_search_port',          '8080');
        set_option('solr_search_core',          '/solr/omeka/');
        set_option('solr_search_facet_limit',   '25');
        set_option('solr_search_facet_sort',    'count');
        set_option('solr_search_hl',            '1');
        set_option('solr_search_hl_snippets',   '1');
        set_option('solr_search_hl_fragsize',   '250');
        set_option('solr_search_hl_max_analyzed_chars', '51200');
        set_option('solr_search_display_private_items', '1');
    }


    /**
     * Clear the global options.
     */
    protected function _clearOptions()
    {
        delete_option('solr_search_host');
        delete_option('solr_search_port');
        delete_option('solr_search_core');
        delete_option('solr_search_facet_limit');
        delete_option('solr_search_facet_sort');
        delete_option('solr_search_hl');
        delete_option('solr_search_hl_snippets');
        delete_option('solr_search_hl_fragsize');
        delete_option('solr_search_hl_max_analyzed_chars');
        delete_option('solr_search_display_private_items');
    }


}
