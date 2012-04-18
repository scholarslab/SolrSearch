<?php
/**
 * "Falmouth" Theme custom functions
 *
 * Custom functions for the Falmouth Omeka theme
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package   omeka
 * @author    "Scholars Lab"
 * @copyright 2010 The Board and Visitors of the University of Virginia
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @version   $Id$
 * @link      http://www.scholarslab.org
 *
 * PHP version 5
 *
 */
?>

<?php

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
        $query = http_build_query(array(
            'solrq' => $this->_request->getParam('search'),
            'solrfacet' => ''
        ));

        // Redirect.
        $this->_redirect('solr-search/results?' . $query);

    }

    public function indexAction() {

        if ($this->isAjax()) {
            $this->handleJson();
        } else {
            $this->handleHtml();
        }
    }

    private function isAjax() {
        return ($this->getRequest()->isXmlHttpRequest() ||
                (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1'));
    }

    protected function handleHtml() {
        $facets = $this->getSearchFacets();
        $pagination = $this->getPagination();
        $page = $pagination['page'];
        $search_rows = $pagination['per_page'];
        $start = ($page - 1) * $search_rows;

        $results = $this->search($facets, $start, $search_rows);

        $this->updatePagination($pagination, $results->response->numFound);
        $this->view->assign(array(
            'results'    => $results,
            'pagination' => $pagination,
            'page'       => $page
        ));

        $this->view->facets = $facets;
    }

    protected function handleJson() {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $facets = $this->getSearchFacets();
        $results = $this->search($facets, 0, 1500);
        $this->view->assign(array(
            'results' => $results
        ));
        $this->view->facets = $facets;
        $this->_helper->viewRenderer('ajax');
    }

    private function getSearchFacets() {
        //get facets
        $facets = array();

        $db = get_db();
        $facetList = $db
            ->getTable('SolrSearch_Facet')
            ->findBySql('is_facet = ?', array('1'));
        foreach ($facetList as $facet) {
            if ($facet['element_set_id'] != NULL) {
                $elements = $db
                    ->getTable('Element')
                    ->findBySql(
                        'element_set_id = ?',
                        array($facet['element_set_id'])
                    );
                foreach ($elements as $element) {
                    if ($element['name'] == $facet['name']) {
                        $facets[] = $element['id'] . '_s';
                    }
                }
            } else {
                $facets[] = $facet['name'];
            }
        }

        return $facets;
    }

    private function getSearchParameters($facets) {
        $displayFields = $this->getDisplayableFields();
        $sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : '';

        if (!empty($facets)) {
            $params = array(
                'fl'             => $displayFields,
                'facet'          => 'true',
                'facet.mincount' => 1,
                'facet.limit'    => SOLR_FACET_LIMIT,
                'facet.field'    => $facets,
                'hl'             => get_option('solr_search_hl'),
                'hl.snippets'    => get_option('solr_search_snippets'),
                'hl.fragsize'    => get_option('solr_search_fragsize'),
                'sort'           => $sort,
                'facet.sort'     => get_option('solr_search_facet_sort')
            );
        } else {
            $params = array(
                'fl'   => $displayFields,
                'sort' => $sort
            );
        }

        return $params;
    }

    private function getPagination($numFound=0) {
        $request = $this->getRequest();
        $page = $request->get('page') or $page = 1;
        $rows = get_option('solr_search_rows') or $rows = SOLR_ROWS;
        $paginationUrl = $this->getRequest()->getBaseUrl() . '/results/';

        $pagination = array(
            'page'          => $page,
            'per_page'      => $rows,
            'total_results' => $numFound,
            'link'          => $paginationUrl
        );

        Zend_Registry::set('pagination', $pagination);

        return $pagination;
    }

    private function updatePagination($pagination, $numFound) {
        $pagination['total_results'] = $numFound;
        Zend_Registry::set('pagination', $pagination);
        return $pagination;
    }

    private function search($facets, $offset=0, $limit=10) {
        $solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);
        $query = SolrSearch_ViewHelpers::createQuery(SolrSearch_QueryHelpers::getParams());
        $params = $this->getSearchParameters($facets);

        $results = $solr->search($query, $offset, $limit, $params);

        return $results;
    }

    //get the displayable fields from the Solr table, which is passed to the view to restrict which fields appear in the results
    private function getDisplayableFields() {
        $db = get_db();
        $displayFields = $db->getTable('SolrSearch_Facet')->findBySql('is_displayed = ?', array('1'));

        $fields .= 'title,id';
        foreach ($displayFields as $k=>$displayField){
            //pass field accordingly, depending on whether it is an element or collection/tag
            if ($displayField['element_id'] != NULL){
                $fields .= ',' . $displayField['element_id'] . '_s';
            } else{
                $fields .= ',' . $displayField['name'];
            }

        }
        return $fields;
    }

}

?>

<?php
/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
