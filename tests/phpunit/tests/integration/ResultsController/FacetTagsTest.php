<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class ResultsControllerTest_FacetTags extends SolrSearch_Case_Default
{


    protected $_isAdminTest = false;


    /**
     * Insert an item with a set of tags.
     *
     * @param string $title The item title.
     * @param string $tags A comma-delimited list of tags.
     * @return Item
     */
    protected function _collitem($title, $tags)
    {
        return insert_item(
            array(
                'public' => true,
                'tags' => $tags
            ), array(
                'Dublin Core' => array(
                    'Title' => array(
                        array('text' => $title, 'html' => false)
                    )
                )
            )
        );
    }


    /**
     * Create tagged items.
     */
    public function setUp()
    {
        parent::setUp();
        $this->item1 = $this->_collitem('Item 1', 'tag1');
        $this->item2 = $this->_collitem('Item 2', 'tag2');
        $this->item3 = $this->_collitem('Item 3', 'tag3');
        $this->item4 = $this->_collitem('Item 4', 'tag2,tag3');
    }


    /**
     * When no facet is applied all facet links and items should be listed.
     */
    public function testNoFacet()
    {

        $this->dispatch('solr-search');

        $tag1Link = $this->_getFacetLink('tag', 'tag1');
        $tag2Link = $this->_getFacetLink('tag', 'tag2');
        $tag3Link = $this->_getFacetLink('tag', 'tag3');

        // Should display all facet links.
        $this->_assertFacetLink($tag1Link, 'tag1');
        $this->_assertFacetLink($tag2Link, 'tag2');
        $this->_assertFacetLink($tag3Link, 'tag3');

        // Should display all items.
        $this->_assertResultLink(record_url($this->item1), 'Item 1');
        $this->_assertResultLink(record_url($this->item2), 'Item 2');
        $this->_assertResultLink(record_url($this->item3), 'Item 3');
        $this->_assertResultLink(record_url($this->item4), 'Item 4');

    }


    /**
     * When the `tag2` facet is applied, the `tag1` facet link should be
     * removed, since none of the items in the new results have that tag, and
     * just the items with `tag2` should be displayed.
     */
    public function testTag2()
    {

        $this->dispatch($this->_getFacetLink('tag', 'tag2'));

        $tag1Link = $this->_getFacetLink('tag', 'tag1');
        $tag2Link = $this->_getFacetLink('tag', 'tag2');
        $tag3Link = $this->_getFacetLink('tag', 'tag3');

        // Should remove the `tag1` facet link.
        $this->_assertNotFacetLink($tag1Link);
        $this->_assertFacetLink($tag2Link, 'tag2');
        $this->_assertFacetLink($tag3Link, 'tag3');

        // Should list items tagged with `tag2`.
        $this->_assertNotResultLink(record_url($this->item1));
        $this->_assertResultLink(record_url($this->item2), 'Item 2');
        $this->_assertNotResultLink(record_url($this->item3));
        $this->_assertResultLink(record_url($this->item4), 'Item 4');

    }


    /**
     * When the `tag3` facet is applied, the `tag1` facet link should be
     * removed, since none of the items in the new results have that tag, and
     * just the items with `tag3` should be displayed.
     */
    public function testTag3()
    {

        $this->dispatch($this->_getFacetLink('tag', 'tag3'));

        $tag1Link = $this->_getFacetLink('tag', 'tag1');
        $tag2Link = $this->_getFacetLink('tag', 'tag2');
        $tag3Link = $this->_getFacetLink('tag', 'tag3');

        // Should remove the `tag1` facet link.
        $this->_assertNotFacetLink($tag1Link);
        $this->_assertFacetLink($tag2Link, 'tag2');
        $this->_assertFacetLink($tag3Link, 'tag3');

        // Should list items tagged with `tag2`.
        $this->_assertNotResultLink(record_url($this->item1));
        $this->_assertNotResultLink(record_url($this->item2));
        $this->_assertResultLink(record_url($this->item3), 'Item 3');
        $this->_assertResultLink(record_url($this->item4), 'Item 4');

    }


    /**
     * When both the `tag2` and `tag3` are applied, the `tag1` facet should be
     * removed and only records with both tags should be displayed.
     */
    public function testTag2And3()
    {

        // Apply `tag2`, then `tag3`.
        $this->dispatch($this->_getFacetLink('tag', 'tag2'));
        $this->resetResponse();
        $this->dispatch($this->_getFacetLink('tag', 'tag3'));

        $tag1Link = $this->_getFacetLink('tag', 'tag1');
        $tag2Link = $this->_getFacetLink('tag', 'tag2');
        $tag3Link = $this->_getFacetLink('tag', 'tag3');

        // Should remove the `tag1` facet link.
        $this->_assertNotFacetLink($tag1Link);
        $this->_assertFacetLink($tag2Link, 'tag2');
        $this->_assertFacetLink($tag3Link, 'tag3');

        // Should list items with `tag2` AND `tag3`.
        $this->_assertNotResultLink(record_url($this->item1));
        $this->_assertNotResultLink(record_url($this->item2));
        $this->_assertNotResultLink(record_url($this->item3));
        $this->_assertResultLink(record_url($this->item4), 'Item 4');

    }


}
