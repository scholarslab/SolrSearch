<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

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
     */
    protected function _item($title, $tags)
    {
        return insert_item(array('public' => true, 'tags' => $tags), array(
            'Dublin Core' => array (
                'Title' => array(
                    array('text' => $title, 'html' => false)
                )
            )
        ));
    }


    /**
     * Get an XPath-compliant facet URL.
     *
     * @param string $field The field name.
     * @param string $value The facet value.
     * @param string The XPath-compliant URL.
     */
    protected function _addFacet($field, $value)
    {
        return htmlspecialchars_decode(
            SolrSearch_Helpers_Facet::addFacet($field, $value)
        );
    }


    /**
     * Assert the presence of a facet link.
     *
     * @param string $url The facet URL.
     * @param string $text The facet value.
     */
    protected function _assertFacetLink($url, $value)
    {
        $this->assertXpathContentContains(
            "//a[@href='$url'][@class='facet-value']", $value
        );
    }


    /**
     * Assert the presence of a result link.
     *
     * @param string $url The result URL.
     * @param string $title The result title.
     */
    protected function _assertResultLink($url, $title)
    {
        $this->assertXpathContentContains(
            "//a[@href='$url'][@class='result-title']", $title
        );
    }


    /**
     * Create tagged items.
     */
    public function setUp()
    {
        parent::setUp();
        $this->item1 = $this->_item('Item 1', 'tag1');
        $this->item2 = $this->_item('Item 2', 'tag2,tag2');
        $this->item3 = $this->_item('Item 3', 'tag1,tag2,tag3');
    }


}
