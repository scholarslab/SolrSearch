<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_ResultsController
    extends Omeka_Controller_AbstractActionController
{


    /**
     * Intercept queries from the simple search form.
     */
    public function interceptorAction()
    {
        $this->_redirect('solr-search/results?'.http_build_query(array(
            'solrq' => $this->_request->getParam('query')
        )));
    }


    /**
     * Default index action.
     */
    public function indexAction()
    {

        // Get a list of active facets.
        $facets = $this->_getSearchFacets();

        // Get the pagination settings.
        $pagination = $this->_getPagination();

        // Get the starting offset.
        $start = ($pagination['page']-1) * $pagination['per_page'];

        // Execute the query.
        $results = $this->_search($facets, $start, $pagination['per_page']);

        // Update the pagination in the Zend registry.
        $this->_updatePagination($pagination, $results->response->numFound);

        $this->view->results = $results;
        $this->view->facets  = $facets;

    }


    /**
     * Retrieve pagination settings from the database
     *
     * @param int $numFound Number of results
     * @return int Pagination settings
     */
    private function _getPagination($numFound=0)
    {

        // Get the current page and page length.
        $page = $this->_request->page ? $this->_request->page : 1;
        $rows = get_option('per_page_public');

        $pagination = array(
            'page'          => $page,
            'total_results' => $numFound,
            'per_page'      => $rows
        );

        // Set the pagination in the registry.
        Zend_Registry::set('pagination', $pagination);
        return $pagination;

    }


    /**
     * Update the pagination setting
     *
     * @param int $pagination Number of results per page
     * @param int $numFound   Total number of results in query
     * @return int Pagination setting
     */
    private function _updatePagination($pagination, $numFound)
    {
        $pagination['total_results'] = $numFound;
        Zend_Registry::set('pagination', $pagination);
        return $pagination;
    }


    /**
     * Pass setting to Solr search
     *
     * @param array $facets Facet fields
     * @param int   $offset Results offset
     * @param int   $limit  Limit per page
     * @return SolrResultDoc Solr results
     */
    private function _search($facets, $offset=0, $limit=10)
    {

        // Connect to Solr.
        $solr = SolrSearch_Helpers_Index::connect();

        // Form the query.
        $query = SolrSearch_Helpers_Query::createQuery(
            SolrSearch_Helpers_Query::getParams()
        );

        // Get the parameters.
        $params = $this->_getSearchParameters($facets);

        // Execute the query.
        $results = $solr->search($query, $offset, $limit, $params);

        return $results;

    }


    /**
     * Construct the Solr search parameters.
     *
     * @param array $facets Array containing facet fields
     * @return array Array of fields to pass to Solr
     */
    private function _getSearchParameters($facets)
    {

        $displayedFields    = $this->_getDisplayedFields();
        $hiddenFields       = $this->_getHiddenFields();

        if (!empty($facets)) {
            $params = array(
                'fl'             => "$displayedFields,$hiddenFields",
                'facet'          => 'true',
                'facet.field'    => $facets,
                'facet.mincount' => 1,
                'facet.limit'    => get_option('solr_search_facet_limit'),
                'hl'             => get_option('solr_search_hl'),
                'hl.snippets'    => get_option('solr_search_hl_snippets'),
                'hl.fragsize'    => get_option('solr_search_hl_fragsize'),
                'facet.sort'     => get_option('solr_search_facet_sort'),
                'hl.fl'          => $displayedFields
            );
        }

        else $params = array('fl' => $displayedFields);

        return $params;

    }


    /**
     * Get the displayable fields from the Solr table, which is passed to the
     * view to restring fields that appear in the results
     *
     * @return string Fields to display
     */
    private function _getDisplayedFields()
    {
        $db = get_db();
        $displayFields = $db->getTable('SolrSearchFacet')->findBySql(
            'is_indexed = ?', array('1')
        );

        $fields = array('id', 'title');
        foreach ($displayFields as $k => $displayField) {
            $fields[] = $displayField['name'];
        }
        return implode(',', $fields);
    }


    /**
     * This returns all fields that need to be included in the output from
     * Solr, but aren't displayed.
     *
     * @return string $fields A comma-delimited list of fields.
     */
    private function _getHiddenFields()
    {
        $fields = "image,title,url,model,modelid";
        return $fields;
    }


    /**
     * Retrive search facet settings from the database.
     *
     * @return array Array containing facet fields
     */
    private function _getSearchFacets()
    {

        $facets = array();

        $db = get_db();
        $facetList = $db
            ->getTable('SolrSearchFacet')
            ->findBySql('is_facet = ?', array('1'));
        foreach ($facetList as $facet) {
            $facets[] = $facet->name;
        }

        natcasesort($facets);
        return $facets;
    }


}
