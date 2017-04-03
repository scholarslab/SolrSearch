<?php

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
        $this->_fields = $this->_helper->db->getTable('SolrSearchField');
    }


    /**
     * Intercept queries from the simple search form.
     */
    public function interceptorAction()
    {
        $this->_redirect('solr-search?'.http_build_query(array(
            'q' => $this->_request->getParam('query')
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


        // determine whether to display private items or not
        // items will only be displayed if:
        // solr_search_display_private_items has been enabled in the Solr Search admin panel
        // user is logged in
        // user_role has sufficient permissions

        $user = current_user();
        if(get_option('solr_search_display_private_items')
            && $user
            && is_allowed('Items','showNotPublic')) {
            // limit to public items
            $limitToPublicItems = false;
        } else {
            $limitToPublicItems = true;
        }

        // Execute the query.
        $results = $this->_search($start, $limit, $limitToPublicItems);

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
    protected function _search($offset, $limit, $limitToPublicItems = true)
    {

        // Connect to Solr.
        $solr = SolrSearch_Helpers_Index::connect();

        // Get the parameters.
        $params = $this->_getParameters();

        // Construct the query.
        $query = $this->_getQuery($limitToPublicItems);

        // Execute the query.
        return $solr->search($query, $offset, $limit, $params);

    }


    /**
     * Form the complete Solr query.
     *
     * @return string The Solr query.
     */
    protected function _getQuery($limitToPublicItems = true)
    {

        // Get the `q` GET parameter in a group.
        $q = '(' . trim($this->_request->q) . ')';

        // If no `q` GET parameter was specified, match everything.
        if ($q === '()') {
            $q = '*:*';
        }

        // Add the `q` GET parameter to the query as a group.
        $query[] = $q;

        // Get the `facet` GET parameter.
        $facet = trim($this->_request->facet);

        // If the `facet` GET parameter was specified, add each individual
        // phrase to the query after marking that phrase as required.
        if (!empty($facet)) {
            foreach (explode(' AND ', $facet) as $field) {
                $query[] = '+' . $field;
            }
        }

        // Add public item limit to the array of query phrases if necessary.
        if ($limitToPublicItems) {
            $query[] = '+public:"true"';
        }

        // Use the Extended DisMax query parser, and combine all query phrases
        // with the AND operator.
        return '{!edismax}' . implode(' AND ', $query);

    }


    /**
     * Construct the Solr search parameters.
     *
     * @return array Array of fields to pass to Solr
     */
    protected function _getParameters()
    {

        // Get a list of active facets.
        $facets = $this->_fields->getActiveFacetKeys();

        return array(

            'facet'               => 'true',
            'facet.field'         => $facets,
            'facet.mincount'      => 1,
            'facet.limit'         => get_option('solr_search_facet_limit'),
            'facet.sort'          => get_option('solr_search_facet_sort'),
            'hl'                  => get_option('solr_search_hl')?'true':'false',
            'hl.snippets'         => get_option('solr_search_hl_snippets'),
            'hl.fragsize'         => get_option('solr_search_hl_fragsize'),
            'hl.maxAnalyzedChars' => get_option('solr_search_hl_max_analyzed_chars'),
            'hl.fl'               => '*_t'

        );

    }


}
