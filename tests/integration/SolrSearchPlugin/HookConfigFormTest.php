<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class NeatlinePluginTest_HookAfterSaveItem extends SolrSearch_Test_AppTestCase
{


    /**
     * `hookConfigForm` should display the plugin configuration form.
     */
    public function testHookConfigForm()
    {

        $this->dispatch('plugins/config?name=SolrSearch');

        // Solr host:
        $this->assertXpath(
            '//input
            [@name="solr_search_server"]
            [@value="' . get_option('solr_search_server') . '"]'
        );

        // Solr port:
        $this->assertXpath(
            '//input
            [@name="solr_search_port"]
            [@value="'. get_option('solr_search_port') . '"]'
        );

        // Core URL:
        $this->assertXpath(
            '//input
            [@name="solr_search_core"]
            [@value="'. get_option('solr_search_core') . '"]'
        );

        // Facet sort:
        $this->assertXpath(
            '//select
            [@name="solr_search_facet_sort"]
            /option
            [@value="'. get_option('solr_search_facet_sort') . '"]
            [@selected="selected"]'
        );

        // Facet count:
        $this->assertXpath(
            '//input
            [@name="solr_search_facet_limit"]
            [@value="'. get_option('solr_search_facet_limit') . '"]'
        );

    }


}
