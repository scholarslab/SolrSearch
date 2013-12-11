<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class SolrSearchPluginTest_HookConfig extends SolrSearch_Test_AppTestCase
{


    /**
     * Set plugin options.
     */
    public function setUp()
    {
        parent::setUp();
        set_option('solr_search_server', 'server');
        set_option('solr_search_port', 'port');
        set_option('solr_search_core', '/core/');
        set_option('solr_search_facet_sort', 'count');
        set_option('solr_search_facet_limit', '25');
    }


    /**
     * A Solr host is required.
     */
    public function testNoHostError()
    {

        // Missing host.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_server'        => '',
            'solr_search_port'          => $this->config->port,
            'solr_search_core'          => $this->config->url,
            'solr_search_facet_sort'    => 'count',
            'solr_search_facet_limit'   => '25'
        ));

        $this->dispatch('plugins/config?name=SolrSearch');

        // Should not set option.
        $this->assertEquals('server', get_option('solr_search_server'));

        // Should flash error.
        $this->assertXPath('//li[@class="error"]');

    }


    /**
     * A Solr port is required.
     */
    public function testNoPortError()
    {

        // Missing port.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_server'        => $this->config->host,
            'solr_search_port'          => '',
            'solr_search_core'          => $this->config->url,
            'solr_search_facet_sort'    => 'count',
            'solr_search_facet_limit'   => '25'
        ));

        $this->dispatch('plugins/config?name=SolrSearch');

        // Should not set option.
        $this->assertEquals('port', get_option('solr_search_port'));

        // Should flash error.
        $this->assertXPath('//li[@class="error"]');

    }


    /**
     * A Solr core is required.
     */
    public function testNoCoreError()
    {

        // Missing core.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_server'        => $this->config->host,
            'solr_search_port'          => $this->config->port,
            'solr_search_core'          => '',
            'solr_search_facet_sort'    => 'count',
            'solr_search_facet_limit'   => '25'
        ));

        $this->dispatch('plugins/config?name=SolrSearch');

        // Should not set option.
        $this->assertEquals('/core/', get_option('solr_search_core'));

        // Should flash error.
        $this->assertXPath('//li[@class="error"]');

    }


    /**
     * The core must be of format `/core/`.
     */
    public function testInvalidCoreError()
    {

        // Invalid core URL.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_server'        => $this->config->host,
            'solr_search_port'          => $this->config->port,
            'solr_search_core'          => 'invalid',
            'solr_search_facet_sort'    => 'count',
            'solr_search_facet_limit'   => '25'
        ));

        $this->dispatch('plugins/config?name=SolrSearch');

        // Should not set option.
        $this->assertEquals('/core/', get_option('solr_search_core'));

        // Should flash error.
        $this->assertXPath('//li[@class="error"]');

    }


    /**
     * A facet length is required.
     */
    public function testNoFacetLengthError()
    {

        // Missing facet length.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_server'        => $this->config->host,
            'solr_search_port'          => $this->config->port,
            'solr_search_core'          => $this->config->url,
            'solr_search_facet_sort'    => 'count',
            'solr_search_facet_limit'   => ''
        ));

        $this->dispatch('plugins/config?name=SolrSearch');

        // Should not set option.
        $this->assertEquals('25', get_option('solr_search_facet_limit'));

        // Should flash error.
        $this->assertXPath('//li[@class="error"]');

    }


    /**
     * The facet length must be a number.
     */
    public function testInvalidFacetLengthError()
    {

        // Invalid facet limit.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_server'        => $this->config->host,
            'solr_search_port'          => $this->config->port,
            'solr_search_core'          => $this->config->url,
            'solr_search_facet_sort'    => 'count',
            'solr_search_facet_limit'   => 'invalid'
        ));

        $this->dispatch('plugins/config?name=SolrSearch');

        // Should not set option.
        $this->assertEquals('25', get_option('solr_search_facet_limit'));

        // Should flash error.
        $this->assertXPath('//li[@class="error"]');

    }


    /**
     * Valid settings should be applied.
     */
    public function testSuccess()
    {

        $this->request->setMethod('POST')->setPost(array(
            'solr_search_server'        => $this->config->host,
            'solr_search_port'          => $this->config->port,
            'solr_search_core'          => $this->config->url,
            'solr_search_facet_sort'    => 'index',
            'solr_search_facet_limit'   => '30'
        ));

        $this->dispatch('plugins/config?name=SolrSearch');

        $server = get_option('solr_search_server');
        $port   = get_option('solr_search_port');
        $core   = get_option('solr_search_core');
        $sort   = get_option('solr_search_facet_sort');
        $limit  = get_option('solr_search_facet_limit');

        // Should update options.
        $this->assertEquals($this->config->host, $server);
        $this->assertEquals($this->config->port, $port);
        $this->assertEquals($this->config->url, $core);
        $this->assertEquals('index', $sort);
        $this->assertEquals('30', $limit);

    }


}
