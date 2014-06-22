<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class ResultsControllerTest_InterceptSearch extends SolrSearch_Case_Default
{


    protected $_isAdminTest = false;


    /**
     * The simple search form should point to the Solr Search interceptor.
     */
    public function testOverrideSearchFormAction()
    {

        $this->dispatch('');

        // Get the interceptor URL.
        $url = public_url('solr-search/results/interceptor');

        // Should override the default search action.
        $this->assertXpath("//form[@id='search-form'][@action='$url']");

    }


    /**
     * When a query is submitted to the simple search API, the request should
     * be redirected to the Solr Search results route and the query should be
     * forwarded as the `q` parameter.
     */
    public function testRedirectWithQuery()
    {

        $_GET['query'] = 'query';

        // Search for 'query'.
        $this->dispatch('solr-search/results/interceptor');

        // Should redirect with the `q` parameter.
        $this->assertRedirectTo('/solr-search?q=query');

    }


}
