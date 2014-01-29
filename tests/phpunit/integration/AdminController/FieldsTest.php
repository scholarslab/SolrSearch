<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class AdminControllerTest_Fields extends SolrSearch_Test_AppTestCase
{


    /**
     * TODO|test
     */
    public function testFields()
    {

        $this->request->setMethod('POST')->setPost(array('facets' => array(
            'facets' => array(
                '1000' => array(
                    'facetid' => 1,
                    'options' => array('is_displayed', 'is_facet'),
                    'label' => 'Tag'
                )
            )
        )));

        $this->dispatch('solr-search/fields');
        print_r($this->facetsTable->find(1));

    }


}
