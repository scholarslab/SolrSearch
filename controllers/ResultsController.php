<?php

require_once 'Omeka/Controller/Action.php';

class SolrSearch_ResultsController extends Omeka_Controller_Action
{
	public function pageAction()
	{
		$limit = SOLR_ROWS;
		$solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);
		
		// Get the request object
		$request = $this->getRequest();
	         
		// Get the non empty request parameters
		$requestParams = $request->getParams();
		
		//get q parameter
		$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
		$page = $request->get('page') or $page = 1;       
		$start = ($page - 1) * 10;
		
		$results = $solr->search($query, $start, $limit);
		$numFound = $results->response->numFound;
	
		$paginationUrl = $this->getRequest()->getBaseUrl() . '/results/';
		//Serve up the pagination
		/*$paginator = Zend_Paginator::factory($numFound);
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($limit);*/
	
		$pagination = array(	'page'          => $page, 
								'per_page'      => $limit, 
								'total_results' => $numFound);
	
		Zend_Registry::set('pagination', $pagination);
		//Zend_Registry::set('total_results', $numFound);
	    	
		$this->view->assign(array('results'=>$results, 'pagination'=>$pagination, 'page'=>$page));
		$displayFields = $this->getDisplayableFields();
		$this->view->displayFields = $displayFields;
	}	
	
	private function getDisplayableFields(){
		$db = get_db();
		$displayFields = $db->getTable('SolrSearch_Facet')->findBySql('is_displayed = ?', array('1'));
		
		$fields = array();
		foreach ($displayFields as $displayField){
			$fields[] = $displayField['element_id'];
		}
		return $fields;
	}	
}


