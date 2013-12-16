<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class AdminControllerTest_Server extends SolrSearch_Test_AppTestCase
{


    /**
     * Set plugin options.
     */
    public function setUp()
    {
        parent::setUp();
        set_option('solr_search_host', 'server');
        set_option('solr_search_port', 'port');
        set_option('solr_search_core', '/core/');
        set_option('solr_search_facet_order', 'count');
        set_option('solr_search_facet_count', '25');
    }


    /**
     * SERVER should display the plugin configuration form.
     */
    public function testMarkup()
    {

        $this->dispatch('solr-search/server');

        // Solr host:
        $this->assertXpath(
            '//input
            [@name="solr_search_host"]
            [@value="' . get_option('solr_search_host') . '"]'
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
            [@name="solr_search_facet_order"]
            /option
            [@value="'. get_option('solr_search_facet_order') . '"]
            [@selected="selected"]'
        );

        // Facet count:
        $this->assertXpath(
            '//input
            [@name="solr_search_facet_count"]
            [@value="'. get_option('solr_search_facet_count') . '"]'
        );

    }


    /**
     * A Solr host is required.
     */
    public function testNoHostError()
    {

        // Missing host.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_host'          => '',
            'solr_search_port'          => $this->config->port,
            'solr_search_core'          => $this->config->core,
            'solr_search_facet_order'   => 'count',
            'solr_search_facet_count'   => '25'
        ));

        $this->dispatch('solr-search/server');

        // Should not set option.
        $this->assertEquals('server', get_option('solr_search_host'));

        // Should flash error.
        $this->assertXpath('//input[@name="solr_search_host"]/
            following-sibling::ul[@class="error"]'
        );

    }


    /**
     * A Solr port is required.
     */
    public function testNoPortError()
    {

        // Missing port.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_host'          => $this->config->host,
            'solr_search_port'          => '',
            'solr_search_core'          => $this->config->core,
            'solr_search_facet_order'   => 'count',
            'solr_search_facet_count'   => '25'
        ));

        $this->dispatch('solr-search/server');

        // Should not set option.
        $this->assertEquals('port', get_option('solr_search_port'));

        // Should flash error.
        $this->assertXpath('//input[@name="solr_search_port"]/
            following-sibling::ul[@class="error"]'
        );

    }


    /**
     * A Solr core is required.
     */
    public function testNoCoreError()
    {

        // Missing core.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_host'          => $this->config->host,
            'solr_search_port'          => $this->config->port,
            'solr_search_core'          => '',
            'solr_search_facet_order'   => 'count',
            'solr_search_facet_count'   => '25'
        ));

        $this->dispatch('solr-search/server');

        // Should not set option.
        $this->assertEquals('/core/', get_option('solr_search_core'));

        // Should flash error.
        $this->assertXpath('//input[@name="solr_search_core"]/
            following-sibling::ul[@class="error"]'
        );

    }


    /**
     * The core must be of format `/core/`.
     */
    public function testInvalidCoreError()
    {

        // Invalid core URL.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_host'          => $this->config->host,
            'solr_search_port'          => $this->config->port,
            'solr_search_core'          => 'invalid',
            'solr_search_facet_order'   => 'count',
            'solr_search_facet_count'   => '25'
        ));

        $this->dispatch('solr-search/server');

        // Should not set option.
        $this->assertEquals('/core/', get_option('solr_search_core'));

        // Should flash error.
        $this->assertXpath('//input[@name="solr_search_core"]/
            following-sibling::ul[@class="error"]'
        );

    }


    /**
     * A facet length is required.
     */
    public function testNoFacetLengthError()
    {

        // Missing facet length.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_host'          => $this->config->host,
            'solr_search_port'          => $this->config->port,
            'solr_search_core'          => $this->config->core,
            'solr_search_facet_order'   => 'count',
            'solr_search_facet_count'   => ''
        ));

        $this->dispatch('solr-search/server');

        // Should not set option.
        $this->assertEquals('25', get_option('solr_search_facet_count'));

        // Should flash error.
        $this->assertXpath('//input[@name="solr_search_facet_count"]/
            following-sibling::ul[@class="error"]'
        );

    }


    /**
     * The facet length must be a number.
     */
    public function testInvalidFacetLengthError()
    {

        // Invalid facet limit.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_host'          => $this->config->host,
            'solr_search_port'          => $this->config->port,
            'solr_search_core'          => $this->config->core,
            'solr_search_facet_order'   => 'count',
            'solr_search_facet_count'   => 'invalid'
        ));

        $this->dispatch('solr-search/server');

        // Should not set option.
        $this->assertEquals('25', get_option('solr_search_facet_count'));

        // Should flash error.
        $this->assertXpath('//input[@name="solr_search_facet_count"]/
            following-sibling::ul[@class="error"]'
        );

    }


    /**
     * Valid settings should be applied.
     */
    public function testSuccess()
    {

        $this->request->setMethod('POST')->setPost(array(
            'solr_search_host'          => $this->config->host,
            'solr_search_port'          => $this->config->port,
            'solr_search_core'          => $this->config->core,
            'solr_search_facet_order'   => 'index',
            'solr_search_facet_count'   => '30'
        ));

        $this->dispatch('solr-search/server');

        $host   = get_option('solr_search_host');
        $port   = get_option('solr_search_port');
        $core   = get_option('solr_search_core');
        $order  = get_option('solr_search_facet_order');
        $count  = get_option('solr_search_facet_count');

        // Should update options.
        $this->assertEquals($this->config->host, $host);
        $this->assertEquals($this->config->port, $port);
        $this->assertEquals($this->config->core, $core);
        $this->assertEquals('index', $order);
        $this->assertEquals('30', $count);

    }


}
