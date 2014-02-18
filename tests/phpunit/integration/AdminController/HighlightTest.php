<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class AdminControllerTest_Highlight extends SolrSearch_Case_Default
{


    /**
     * HIGHLIGHT should display the hit highlighting form.
     */
    public function testMarkup()
    {

        $this->dispatch('solr-search/highlight');

        // Highlighting:
        $this->assertXpath(
            '//select
            [@name="solr_search_hl"]
            /option
            [@value="'. get_option('solr_search_hl') . '"]
            [@selected="selected"]'
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

        $this->dispatch('solr-search/highlight');

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

        $this->dispatch('solr-search/highlight');

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

        $this->dispatch('solr-search/highlight');

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

        $this->dispatch('solr-search/highlight');

        // Should not set option.
        $this->assertEquals('250', get_option('solr_search_hl_fragsize'));

        // Should flash error.
        $this->_assertFormError('solr_search_hl_fragsize');

    }


    /**
     * Valid settings should be applied.
     */
    public function testSuccess()
    {

        set_option('solr_search_hl',            'true');
        set_option('solr_search_hl_snippets',   '1');
        set_option('solr_search_hl_fragsize',   '250');

        $this->request->setMethod('POST')->setPost(array(
            'solr_search_hl'            => 'false',
            'solr_search_hl_snippets'   => '2',
            'solr_search_hl_fragsize'   => '300'
        ));

        $this->dispatch('solr-search/highlight');

        $hl         = get_option('solr_search_hl');
        $snippets   = get_option('solr_search_hl_snippets');
        $fragsize   = get_option('solr_search_hl_fragsize');

        // Should update options.
        $this->assertEquals('false',    $hl);
        $this->assertEquals('2',        $snippets);
        $this->assertEquals('300',      $fragsize);

    }


}
