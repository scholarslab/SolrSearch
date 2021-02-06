<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class AdminControllerTest_Collections extends SolrSearch_Case_Default
{

    /**
     * Create a collection.
     *
     * @param string $title The collection title.
     * @return Collection
     */
    protected function _collection($title, $public)
    {
        return insert_collection(
            array('public' => $public),
            array(
                'Dublin Core' => array(
                    'Title' => array(
                        array('text' => $title, 'html' => false)
                    )
                )
            )
        );
    }


    public function setUpLegacy()
    {
        parent::setUpLegacy();

        $this->pubColl  = $this->_collection('public collection',  TRUE );
        $this->privColl = $this->_collection('private collection', FALSE);

        $this->opt = get_option('solr_search_display_private_items');
    }

    public function tearDownLegacy()
    {
        set_option('solr_search_display_private_items', $this->opt);

        $this->pubColl ->delete();
        $this->privColl->delete();

        parent::tearDownLegacy();
    }

    /**
     * If 'Display private items' is checked, also display private collections
     * for exclusion.
     */
    public function testDisplayPrivateCollectionsChecked()
    {
        set_option('solr_search_display_private_items', '1');

        $this->dispatch('solr-search/collections');

        // echo $this->getResponse()->getBody();

        $this->assertQueryContentContains(
            "//dd[@id='solrexclude-element']/label",
            "public collection"
        );
        $this->assertQueryContentContains(
            "//dd[@id='solrexclude-element']/label",
            "private collection"
        );
    }

    /**
     * If 'Display private items' is not checked, do not display private
     * collections for exclusion.
     */
    public function testDisplayPrivateCollectionsNotChecked()
    {
        set_option('solr_search_display_private_items', '0');

        $this->dispatch('solr-search/collections');

        $this->assertQueryContentContains(
            "//dd[@id='solrexclude-element'][1]/label",
            "public collection"
        );
        $this->assertNotQuery(
            "//dd[@id='solrexclude-element'][2]/label"
        );
    }

}
