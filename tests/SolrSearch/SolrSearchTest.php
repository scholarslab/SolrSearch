<?php

require_once dirname(__FILE__) . '/../../lib/SolrSearch/QueryManager.php';
require_once dirname(__FILE__) . '/../../../../application/libraries/globals.php';
require_once dirname(__FILE__) . '/../../../../application/helpers/Functions.php';
require_once dirname(__FILE__) . '/../../../../application/helpers/StringFunctions.php';

class SolrSearch_SolrSearchTest extends PHPUnit_Framework_TestCase
{

    private function _makeQueryManager(
        $extra=null, $query=null, $uri='http://test.base.org/'
    ) {
        if ($query === null) {
            $query = array(
                'solrq'     => 'house',
                'solrfacet' => 'tag:"Residential" AND date:[* TO *]'
            );
        }
        return new SolrSearch_QueryManager($query, $uri, $extra);
    }

    public function testGetQuery()
    {
        $mgr = $this->_makeQueryManager();
        $this->assertEquals('house', $mgr->getQuery());
    }

    public function testGetFacets()
    {
        $mgr = $this->_makeQueryManager();
        $facets = $mgr->getFacets();

        $this->assertInternalType('array', $facets);
        $this->assertArrayHasKey('tag', $facets);
        $this->assertEquals('"Residential"', $facets['tag']);
        $this->assertArrayHasKey('date', $facets);
        $this->assertEquals('[* TO *]', $facets['date']);
    }

    public function testIsEmptyFalse() {
        $mgr = $this->_makeQueryManager();
        $this->assertFalse($mgr->isEmpty());
    }

    public function testIsEmptyTrue() {
        $mgr = $this->_makeQueryManager(null, array());
        $this->assertTrue($mgr->isEmpty());
    }

    public function testGetFacetParameter()
    {
        $mgr = $this->_makeQueryManager();

        $this->assertEquals('"Residential"', $mgr->getFacetParameter('tag'));
        $this->assertEquals('[* TO *]',      $mgr->getFacetParameter('date'));
    }

    public function testHasFacet()
    {
        $mgr = $this->_makeQueryManager();

        $this->assertTrue($mgr->hasFacet('tag'));
        $this->assertFalse($mgr->hasFacet('nope'));
    }

    public function testGetBaseUrl()
    {
        $mgr = $this->_makeQueryManager();

        $this->assertEquals('http://test.base.org/', $mgr->getBaseUrl());
    }

    public function testMakeLink()
    {
        $mgr = $this->_makeQueryManager();

        $this->assertEquals(
            'http://test.base.org/?solrq=house&solrfacet=tag:%22Residential%22+AND+date:%5B%2A+TO+%2A%5D',
            $mgr->makeLink()
        );
    }

    public function testMakeLinkExtra()
    {
        $extra = array(
            'page'   => 4,
            'format' => 'json'
        );
        asort($extra);
        $mgr = $this->_makeQueryManager($extra);

        $this->assertEquals(
            'http://test.base.org/?solrq=house&solrfacet=tag:%22Residential%22+AND+date:%5B%2A+TO+%2A%5D&format=json&page=4',
            $mgr->makeLink()
        );
    }

    public function testMakeLinkAddQuery()
    {
        $mgr = $this->_makeQueryManager();

        $this->assertEquals(
            'http://test.base.org/?solrq=home&solrfacet=tag:%22Residential%22+AND+date:%5B%2A+TO+%2A%5D',
            $mgr->makeLinkAddQuery('home')
        );
    }

    public function testMakeLinkRemoveQuery()
    {
        $mgr = $this->_makeQueryManager();

        $this->assertEquals(
            'http://test.base.org/?solrfacet=tag:%22Residential%22+AND+date:%5B%2A+TO+%2A%5D',
            $mgr->makeLinkRemoveQuery()
        );
    }

    public function testMakeLinkAddFacet()
    {
        $mgr = $this->_makeQueryManager();

        $this->assertEquals(
            'http://test.base.org/?solrq=house&solrfacet=tag:%22Residential%22+AND+date:%5B%2A+TO+%2A%5D+AND+image:%5B%2A+TO+%2A%5D',
            $mgr->makeLinkAddFacet('image', '[* TO *]')
        );

        $this->assertNull($mgr->getFacetParameter('image'));
    }

    public function testMakeLinkRemoveFacet()
    {
        $mgr = $this->_makeQueryManager();

        $this->assertEquals(
            'http://test.base.org/?solrq=house&solrfacet=tag:%22Residential%22',
            $mgr->makeLinkRemoveFacet('date')
        );

        $facets = $mgr->getFacets();
        $this->assertArrayHasKey('date', $facets);
        $this->assertEquals('[* TO *]', $facets['date']);
    }

}

?>
