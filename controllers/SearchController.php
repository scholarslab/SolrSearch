<?php

require_once 'Omeka/Controller/Action.php';

class SolrSearch_SearchController extends Omeka_Controller_Action
{
	public function resultsAction()
	{
	$limit = SOLR_ROWS;
	$solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);
	
	$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
	$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : false;
	
	if ($page == false){
		$page = 1;
		$start = 0;
	}
	else{
		$start = ($page - 1) * $limit;
	}

	$results = $solr->search($query, $start, $limit);
	$numFound = $results->response->numFound;
	$paginationUrl = $this->getRequest()->getBaseUrl() . '/results/';
	//Serve up the pagination
	/*$paginator = Zend_Paginator::factory($numFound);
	$paginator->setCurrentPageNumber($page);
	$paginator->setItemCountPerPage($limit);*/

	$pagination = array('menu'          => null, 
                             'page'          => $page, 
                             'per_page'      => $limit, 
                             'total_results' => $numFound, 
                             'link'          => $paginationUrl);
    
	Zend_Registry::set('pagination', $pagination);
	Zend_Registry::set('total_results', $numFound);
    
	$this->view->assign(array('results'=>$results, 'pagination'=>$pagination));
	}

 /**
      * Returns an array with the search results based on the parameters in the request
      * 
      * @return array
      **/
    /* private function _getSearchResults()
     {
         // Get the request object
         $request = $this->getRequest();
         
         // Get the non empty request parameters
         $requestParams = $request->getParams();

         // Determine the result page
         $resultPage = $request->get('page') or $resultPage = 1;         
         
         // Determine the display search query
         $displaySearchQuery = '';
         if (!isset($requestParams['model'])) {
             $displaySearchQuery = trim($requestParams['search']);
         }
          
         // Get the search hits
         if ($search = LuceneSearch_Search::getInstance()) {
             $hits = $search->getLuceneSearchHits($requestParams);
         } else {
             $hits = array();
         }
         
         // Get the total number of hits
         $hitCount = count($hits);
                   
         // Get the hits per page        
         $hitsPerPage = $this->_getHitsPerPage();
         
         // Wrap a paginator around the hits
         $paginator = Zend_Paginator::factory($hits);
         $paginator->setCurrentPageNumber($resultPage);
         $paginator->setItemCountPerPage($hitsPerPage);

         return array(
             'display_search_query' => $displaySearchQuery,
             'hits' => $paginator,
             'total_results' => $hitCount, 
             'page' => $resultPage, 
             'per_page' => $hitsPerPage);
     }	*/
}


