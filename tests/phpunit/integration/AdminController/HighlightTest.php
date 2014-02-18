<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class AdminControllerTest_Highlight extends SolrSearch_Test_AppTestCase
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
            [@name="solr_search_hl_count"]
            [@value="' . get_option('solr_search_hl_count') . '"]'
        );

        // Snippet size:
        $this->assertXpath(
            '//input
            [@name="solr_search_hl_length"]
            [@value="' . get_option('solr_search_hl_length') . '"]'
        );

    }


    /**
     * A snippet count is required.
     */
    public function testNoSnippetCount()
    {

        set_option('solr_search_hl_count', '1');

        // Missing snippet count.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_hl_count' => ''
        ));

        $this->dispatch('solr-search/highlight');

        // Should not set option.
        $this->assertEquals('1', get_option('solr_search_hl_count'));

        // Should flash error.
        $this->_assertFormError('solr_search_hl_count');

    }


    /**
     * The snippet count must be an integer.
     */
    public function testInvalidSnippetCount()
    {

        set_option('solr_search_hl_count', '1');

        // Missing snippet count.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_hl_count' => 'invalid'
        ));

        $this->dispatch('solr-search/highlight');

        // Should not set option.
        $this->assertEquals('1', get_option('solr_search_hl_count'));

        // Should flash error.
        $this->_assertFormError('solr_search_hl_count');

    }


    /**
     * A snippet length is required.
     */
    public function testNoSnippetLength()
    {

        set_option('solr_search_hl_length', '250');

        // Missing snippet length.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_hl_length' => ''
        ));

        $this->dispatch('solr-search/highlight');

        // Should not set option.
        $this->assertEquals('250', get_option('solr_search_hl_length'));

        // Should flash error.
        $this->_assertFormError('solr_search_hl_length');

    }


    /**
     * The snippet length must be an integer.
     */
    public function testInvalidSnippetLength()
    {

        set_option('solr_search_hl_length', '250');

        // Missing snippet length.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_hl_length' => 'invalid'
        ));

        $this->dispatch('solr-search/highlight');

        // Should not set option.
        $this->assertEquals('250', get_option('solr_search_hl_length'));

        // Should flash error.
        $this->_assertFormError('solr_search_hl_length');

    }


    /**
     * Valid settings should be applied.
     */
    public function testSuccess()
    {

        set_option('solr_search_hl',        'true');
        set_option('solr_search_hl_count',  '1');
        set_option('solr_search_hl_length', '250');

        $this->request->setMethod('POST')->setPost(array(
            'solr_search_hl'        => 'false',
            'solr_search_hl_count'  => '2',
            'solr_search_hl_length' => '300'
        ));

        $this->dispatch('solr-search/highlight');

        $hl         = get_option('solr_search_hl');
        $snippets   = get_option('solr_search_hl_count');
        $fragsize   = get_option('solr_search_hl_length');

        // Should update options.
        $this->assertEquals('false',    $hl);
        $this->assertEquals('2',        $snippets);
        $this->assertEquals('300',      $fragsize);

    }


}
