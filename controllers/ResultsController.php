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
     * Cache the facets table.
     */
    public function init()
    {
        $this->_facets = $this->_helper->db->getTable('SolrSearchFacet');
    }


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
     * Display Solr results.
     */
    public function indexAction()
    {

        // Get pagination settings.
        $limit = get_option('per_page_public');
        $page  = $this->_request->page ? $this->_request->page : 1;
        $start = ($page-1) * $limit;

        // Execute the query.
        $results = $this->_search($start, $limit);

        // Set the pagination.
        Zend_Registry::set('pagination', array(
            'page'          => $page,
            'total_results' => $results->response->numFound,
            'per_page'      => $limit
        ));

        // Push results to the view.
        $this->view->results = $results;

    }


    /**
     * Pass setting to Solr search
     *
     * @param int $offset Results offset
     * @param int $limit  Limit per page
     * @return SolrResultDoc Solr results
     */
    private function _search($offset=0, $limit=10)
    {

        // Connect to Solr.
        $solr = SolrSearch_Helpers_Index::connect();

        // Form the query.
        $query = SolrSearch_Helpers_Query::createQuery(
            SolrSearch_Helpers_Query::getParams()
        );

        // Get the parameters.
        $params = $this->_getSearchParameters();

        // Execute the query.
        $results = $solr->search($query, $offset, $limit, $params);

        return $results;

    }


    /**
     * Construct the Solr search parameters.
     *
     * @return array Array of fields to pass to Solr
     */
    private function _getSearchParameters()
    {

        // Get the field lists.
        $displayed  = $this->_getDisplayedFields();
        $hidden     = $this->_getHiddenFields();

        // Get a list of active facets.
        $facets = $this->_facets->getActiveFacetNames();

        if (!empty($facets)) $params = array(

            'fl'             => "$displayed,$hidden",
            'facet'          => 'true',
            'facet.field'    => $facets,
            'facet.mincount' => 1,
            'facet.limit'    => get_option('solr_search_facet_limit'),
            'facet.sort'     => get_option('solr_search_facet_sort'),
            'hl'             => get_option('solr_search_hl'),
            'hl.snippets'    => get_option('solr_search_hl_snippets'),
            'hl.fragsize'    => get_option('solr_search_hl_fragsize'),
            'hl.fl'          => $displayed

        );

        else $params = array('fl' => $displayed);

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
     * This returns all fields that need to be included in the output from Solr, but aren't displayed.
     *
     * @return string $fields A comma-delimited list of fields.
     */
    private function _getHiddenFields()
    {
        $fields = "image,title,url,model,modelid";
        return $fields;
    }


}
