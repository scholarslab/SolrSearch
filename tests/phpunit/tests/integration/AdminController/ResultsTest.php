<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class AdminControllerTest_Results extends SolrSearch_Case_Default
{


    /**
     * RESULTS should display the hit highlighting form.
     */
    public function testMarkup()
    {

        $this->dispatch('solr-search/results');

        // Highlighting:
        $this->assertXpath(
            '//input
            [@name="solr_search_hl"]
            [@value="'. get_option('solr_search_hl') . '"]
            [@checked="checked"]'
        );

        // Snippets:
        $this->assertXpath(
            '//input
            [@name="solr_search_hl_snippets"]
            [@value="' . get_option('solr_search_hl_snippets') . '"]'
        );

        // Snippet size:
        $this->assertXpath(
            '//input
            [@name="solr_search_hl_fragsize"]
            [@value="' . get_option('solr_search_hl_fragsize') . '"]'
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


    /**
     * A snippet count is required.
     */
    public function testNoSnippetCount()
    {

        set_option('solr_search_hl_snippets', '1');

        // Missing snippet count.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_hl_snippets' => ''
        ));

        $this->dispatch('solr-search/results');

        // Should not set option.
        $this->assertEquals('1', get_option('solr_search_hl_snippets'));

        // Should flash error.
        $this->_assertFormError('solr_search_hl_snippets');

    }


    /**
     * The snippet count must be an integer.
     */
    public function testInvalidSnippetCount()
    {

        set_option('solr_search_hl_snippets', '1');

        // Missing snippet count.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_hl_snippets' => 'invalid'
        ));

        $this->dispatch('solr-search/results');

        // Should not set option.
        $this->assertEquals('1', get_option('solr_search_hl_snippets'));

        // Should flash error.
        $this->_assertFormError('solr_search_hl_snippets');

    }


    /**
     * A snippet length is required.
     */
    public function testNoSnippetLength()
    {

        set_option('solr_search_hl_fragsize', '250');

        // Missing snippet length.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_hl_fragsize' => ''
        ));

        $this->dispatch('solr-search/results');

        // Should not set option.
        $this->assertEquals('250', get_option('solr_search_hl_fragsize'));

        // Should flash error.
        $this->_assertFormError('solr_search_hl_fragsize');

    }


    /**
     * The snippet length must be an integer.
     */
    public function testInvalidSnippetLength()
    {

        set_option('solr_search_hl_fragsize', '250');

        // Missing snippet length.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_hl_fragsize' => 'invalid'
        ));

        $this->dispatch('solr-search/results');

        // Should not set option.
        $this->assertEquals('250', get_option('solr_search_hl_fragsize'));

        // Should flash error.
        $this->_assertFormError('solr_search_hl_fragsize');

    }


    /**
     * A facet count is required.
     */
    public function testNoFacetCountError()
    {

        set_option('solr_search_facet_limit', '25');

        // Missing facet length.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_facet_limit' => ''
        ));

        $this->dispatch('solr-search/results');

        // Should not set option.
        $this->assertEquals('25', get_option('solr_search_facet_limit'));

        // Should flash error.
        $this->_assertFormError('solr_search_facet_limit');

    }


    /**
     * The facet count must be a number.
     */
    public function testInvalidFacetCountError()
    {

        set_option('solr_search_facet_limit', '25');

        // Invalid facet limit.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_facet_limit' => 'invalid'
        ));

        $this->dispatch('solr-search/results');

        // Should not set option.
        $this->assertEquals('25', get_option('solr_search_facet_limit'));

        // Should flash error.
        $this->_assertFormError('solr_search_facet_limit');

    }


    /**
     * Valid settings should be applied.
     */
    public function testSuccess()
    {

        set_option('solr_search_hl',            '1');
        set_option('solr_search_hl_snippets',   '1');
        set_option('solr_search_hl_fragsize',   '250');
        set_option('solr_search_facet_sort',    'count');
        set_option('solr_search_facet_limit',   '25');

        $this->request->setMethod('POST')->setPost(array(
            'solr_search_hl_max_analyzed_chars' => '102400',
            'solr_search_hl'                    => '0',
            'solr_search_hl_snippets'           => '2',
            'solr_search_hl_fragsize'           => '300',
            'solr_search_facet_sort'            => 'index',
            'solr_search_facet_limit'           => '30',
            'solr_search_display_private_items' => '0',
            'submit'                            => 'Save+Settings'
        ));

        $this->dispatch('solr-search/results');

        $hl         = get_option('solr_search_hl');
        $snippets   = get_option('solr_search_hl_snippets');
        $fragsize   = get_option('solr_search_hl_fragsize');
        $sort       = get_option('solr_search_facet_sort');
        $limit      = get_option('solr_search_facet_limit');

        $this->assertXpath("//li[@class='success']");
        $this->assertXpathContentContains(
            "//li[@class='success']",
            "Highlighting options successfully saved!"
        );

        // Should update options.
        $this->assertEquals('0',        $hl);
        $this->assertEquals('2',        $snippets);
        $this->assertEquals('300',      $fragsize);
        $this->assertEquals('index',    $sort);
        $this->assertEquals('30',       $limit);

    }


}
