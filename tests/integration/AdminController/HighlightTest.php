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
     * Set plugin options.
     */
    public function setUp()
    {
        parent::setUp();
        set_option('solr_search_hl', 'true');
        set_option('solr_search_snippets', '1');
        set_option('solr_search_fragsize', '250');
    }


    /**
     * HIGHLIGHT should display the hit highlighting form.
     */
    public function testIndex()
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
            [@name="solr_search_snippets"]
            [@value="' . get_option('solr_search_snippets') . '"]'
        );

        // Snippet size:
        $this->assertXpath(
            '//input
            [@name="solr_search_fragsize"]
            [@value="' . get_option('solr_search_fragsize') . '"]'
        );

    }


    /**
     * A snippet count is required.
     */
    public function testNoSnippetCount()
    {

        // Missing snippet count.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_hl'        => 'true',
            'solr_search_snippets'  => '',
            'solr_search_fragsize'  => '250'
        ));

        $this->dispatch('solr-search/highlight');

        // Should not set option.
        $this->assertEquals('1', get_option('solr_search_snippets'));

        // Should flash error.
        $this->_assertFormError('solr_search_snippets');

    }


    /**
     * The snippet count must be an integer.
     */
    public function testInvalidSnippetCount()
    {

        // Missing snippet count.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_hl'        => 'true',
            'solr_search_snippets'  => 'invalid',
            'solr_search_fragsize'  => '250'
        ));

        $this->dispatch('solr-search/highlight');

        // Should not set option.
        $this->assertEquals('1', get_option('solr_search_snippets'));

        // Should flash error.
        $this->_assertFormError('solr_search_snippets');

    }


    /**
     * A snippet length is required.
     */
    public function testNoSnippetLength()
    {

        // Missing snippet length.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_hl'        => 'true',
            'solr_search_snippets'  => '1',
            'solr_search_fragsize'  => ''
        ));

        $this->dispatch('solr-search/highlight');

        // Should not set option.
        $this->assertEquals('250', get_option('solr_search_fragsize'));

        // Should flash error.
        $this->_assertFormError('solr_search_fragsize');

    }


    /**
     * The snippet length must be an integer.
     */
    public function testInvalidSnippetLength()
    {

        // Missing snippet length.
        $this->request->setMethod('POST')->setPost(array(
            'solr_search_hl'        => 'true',
            'solr_search_snippets'  => '1',
            'solr_search_fragsize'  => 'invalid'
        ));

        $this->dispatch('solr-search/highlight');

        // Should not set option.
        $this->assertEquals('250', get_option('solr_search_fragsize'));

        // Should flash error.
        $this->_assertFormError('solr_search_fragsize');

    }


    /**
     * Valid settings should be applied.
     */
    public function testSuccess()
    {

        $this->request->setMethod('POST')->setPost(array(
            'solr_search_hl'        => 'false',
            'solr_search_snippets'  => '2',
            'solr_search_fragsize'  => '300'
        ));

        $this->dispatch('solr-search/highlight');

        $hl         = get_option('solr_search_hl');
        $snippets   = get_option('solr_search_snippets');
        $fragsize   = get_option('solr_search_fragsize');

        // Should update options.
        $this->assertEquals('false',    $hl);
        $this->assertEquals('2',        $snippets);
        $this->assertEquals('300',      $fragsize);

    }


}
