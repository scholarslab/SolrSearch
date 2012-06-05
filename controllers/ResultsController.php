<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

require_once 'Omeka/Controller/Action.php';

class SolrSearch_ResultsController extends Omeka_Controller_Action
{

    /**
     * Intercept search queries from simple search and redirect with
     * a well-formed SolrSearch request.
     *
     * @return void
     */
    public function interceptorAction()
    {

        // Construct the query parameters.
        $query = http_build_query(
            array(
                'solrq' => $this->_request->getParam('search')
            )
        );

        // Redirect.
        $this->_redirect('solr-search/results?' . $query);

    }

    /**
     * Default index action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->handleHtml();

        //if ($this->isAjax()) {
        //$this->handleJson();
        //} else {
        //$this->handleHtml();
        //}
    }

    /**
     * Parse request to determine if it is an AJAX request
     *
     * @return bool
     */
    private function _isAjax()
    {
        return false;
        //TODO: clean this up
        //return ($this->getRequest()->isXmlHttpRequest() ||
        //(isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1'));
    }

    /**
     * Display results using HTML handler
     *
     * @return void
     */
    protected function handleHtml()
    {
        $facets = $this->_getSearchFacets();
        $pagination = $this->_getPagination();
        $page = $pagination['page'];
        $search_rows = $pagination['per_page'];
        $start = ($page - 1) * $search_rows;

        $results = $this->_search($facets, $start, $search_rows);

        $this->_updatePagination($pagination, $results->response->numFound);
        $this->view->assign(
            array(
                'results'    => $results,
                'pagination' => $pagination,
                'page'       => $page
            )
        );

        $this->view->facets = $facets;
    }

    /**
     * Display result set using JSON handler
     *
     * @return void
     */
    protected function handleJson()
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $facets = $this->_getSearchFacets();
        $results = $this->_search($facets, 0, 1500);
        $this->view->assign(
            array(
                'results' => $results
            )
        );

        $this->view->facets = $facets;
        $this->_helper->viewRenderer('ajax');
    }

    /**
     * Retrive search facet settings from the database
     *
     * @return array Array containing facet fields
     */
    private function _getSearchFacets()
    {
        //get facets
        $facets = array();

        $db = get_db();
        $facetList = $db
            ->getTable('SolrSearch_Facet')
            ->findBySql('is_facet = ?', array('1'));
        foreach ($facetList as $facet) {
            $facets[] = $facet->name;
        }

        natcasesort($facets);
        return $facets;
    }

    /**
     * Retrieve search fields
     *
     * @param array $facets Array containing facet fields
     *
     * @return array Array of fields to pass to Solr
     */
    private function _getSearchParameters($facets)
    {
        $displayFields = $this->_getDisplayableFields();
        $hiddenFields  = $this->_getHiddenFields();

        $fields = $displayFields;
        if ($hiddenFields != null && strlen($hiddenFields) > 0) {
            $fields .= ",$hiddenFields";
        }

        if (!empty($facets)) {
            $params = array(
                'fl'             => $fields,
                'facet'          => 'true',
                'facet.mincount' => 1,
                'facet.limit'    => get_option('solr_search_facet_limit'),
                'facet.field'    => $facets,
                'hl'             => get_option('solr_search_hl'),
                'hl.snippets'    => get_option('solr_search_snippets'),
                'hl.fragsize'    => get_option('solr_search_fragsize'),
                'facet.sort'     => get_option('solr_search_facet_sort'),
                'hl.fl'          => $displayFields
            );
        } else {
            $params = array(
                'fl'   => $displayFields
            );
        }
        return $params;
    }

    /**
     * Retrieve pagination settings from the database
     *
     * @param int $numFound Number of results
     *
     * @return int Pagination settings
     */
    private function _getPagination($numFound=0)
    {
        $request = $this->getRequest();
        $page = $request->get('page') or $page = 1;
        $rows = get_option('solr_search_rows');
        $paginationUrl = $this->getRequest()->getBaseUrl() . '/results/';

        if (! $rows) {
            $rows = get_option('per_page_public') or get_option('solr_search_rows');
        }

        $pagination = array(
            'page'          => $page,
            'per_page'      => $rows,
            'total_results' => $numFound,
            'link'          => $paginationUrl
        );

        Zend_Registry::set('pagination', $pagination);

        return $pagination;
    }

    /**
     * Update the pagination setting
     *
     * @param int $pagination Number of results per page
     * @param int $numFound   Total number of results in query
     *
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
     *
     * @return SolrResultDoc Solr results
     */
    private function _search($facets, $offset=0, $limit=10)
    {
        $solr = new Apache_Solr_Service(
            get_option('solr_search_server'),
            get_option('solr_search_port'),
            get_option('solr_search_core')
        );

        $query = SolrSearch_QueryHelpers::createQuery(
            SolrSearch_QueryHelpers::getParams()
        );

        $params = $this->_getSearchParameters($facets);

        $results = $solr->search($query, $offset, $limit, $params);

        return $results;
    }

    /**
     * Get the displayable fields from the Solr table, which is passed to the
     * view to restring fields that appear in the results
     *
     * @return string Fields to display
     */
    private function _getDisplayableFields()
    {
        $db = get_db();
        $displayFields = $db->getTable('SolrSearch_Facet')->findBySql(
            'is_displayed = ?',
            array('1')
        );

        $fields .= 'title,id';
        foreach ($displayFields as $k => $displayField) {
            //pass field accordingly, depending on whether it is an element or
            //collection/tag
            if ($displayField['element_id'] != null) {
                $fields .= ',' . $displayField['element_id'] . '_s';
            } else {
                $fields .= ',' . strtolower($displayField['name']);
            }

        }
        return $fields;
    }

    /**
     * This returns all fields that need to be included in the output from Solr,
     * but aren't displayed.
     *
     * @return string $fields A comma-delimited list of fields.
     * @author Eric Rochester <erochest@virginia.edu>
     */
    private function _getHiddenFields()
    {
        $fields = "image,title,url,model,modelid";
        return $fields;
    }

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

