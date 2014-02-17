<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class ResultsControllerTest_Interceptor extends SolrSearch_Test_AppTestCase
{


    protected $_isAdminTest = false;


    /**
     * The simple sarch form should point to the Solr Search interceptor.
     */
    public function testOverrideSearchFormAction()
    {

        $this->dispatch('');

        // Should override the default search action.
        $this->assertXpath('//form[@id="search-form"][@action="'.
            url('solr-search/results/interceptor').'"]');

    }


}
