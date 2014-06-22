<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class AdminControllerTest_Server extends SolrSearch_Case_Default
{


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

    }


    /**
     * A Solr host is required.
     */
    public function testNoHostError()
    {

        set_option('solr_search_host', 'server');

        // Missing host.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_host' => ''
        ));

        $this->dispatch('solr-search/server');

        // Should not set option.
        $this->assertEquals('server', get_option('solr_search_host'));

        // Should flash error.
        $this->_assertFormError('solr_search_host');

    }


    /**
     * A Solr port is required.
     */
    public function testNoPortError()
    {

        set_option('solr_search_port', 'port');

        // Missing port.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_port' => ''
        ));

        $this->dispatch('solr-search/server');

        // Should not set option.
        $this->assertEquals('port', get_option('solr_search_port'));

        // Should flash error.
        $this->_assertFormError('solr_search_port');

    }


    /**
     * A Solr core is required.
     */
    public function testNoCoreError()
    {

        set_option('solr_search_core', '/core/');

        // Missing core.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_core' => ''
        ));

        $this->dispatch('solr-search/server');

        // Should not set option.
        $this->assertEquals('/core/', get_option('solr_search_core'));

        // Should flash error.
        $this->_assertFormError('solr_search_core');

    }


    /**
     * The core must be of format `/core/`.
     */
    public function testInvalidCoreError()
    {

        set_option('solr_search_core', '/core/');

        // Invalid core URL.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_core' => 'invalid'
        ));

        $this->dispatch('solr-search/server');

        // Should not set option.
        $this->assertEquals('/core/', get_option('solr_search_core'));

        // Should flash error.
        $this->_assertFormError('solr_search_core');

    }


    /**
     * Valid settings should be applied.
     */
    public function testSuccess()
    {

        set_option('solr_search_host',  'server');
        set_option('solr_search_port',  'port');
        set_option('solr_search_core',  '/core/');

        $this->request->setMethod('POST')->setPost(array(
            'solr_search_host'  => $this->config->server,
            'solr_search_port'  => $this->config->port,
            'solr_search_core'  => $this->config->core
        ));

        $this->dispatch('solr-search/server');

        $server = get_option('solr_search_host');
        $port   = get_option('solr_search_port');
        $core   = get_option('solr_search_core');

        // Should update options.
        $this->assertEquals($this->config->server, $server);
        $this->assertEquals($this->config->port, $port);
        $this->assertEquals($this->config->core, $core);

    }


}
