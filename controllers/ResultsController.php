<?php

require_once 'Omeka/Controller/Action.php';

class SolrSearch_ResultsController extends Omeka_Controller_Action
{
	public function indexAction()
	{
		$rows = SOLR_ROWS;
		$solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);
		
		// Get the request object
		$request = $this->getRequest();
	         
		// Get the non empty request parameters
		$requestParams = $request->getParams();
		
		//get q parameter
		$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
    $page = $request->get('page') or $page = 1;       
    $search_rows = get_option('solr_search_rows'); // search rows/page
		//$start = ($page - 1) * 10;
		$start = ($page - 1) * $search_rows;
		
		//get displayable fields
		$displayFields = $this->getDisplayableFields();
		
		//get facets
		$solrFacets = array();
		$db = get_db();
		$facetList = $db->getTable('SolrSearch_Facet')->findBySql('is_facet = ?', array('1'));
		foreach ($facetList as $facet){
			if ($facet['element_set_id'] != NULL){
				$elements = $db->getTable('Element')->findBySql('element_set_id = ?', array($facet['element_set_id']));
				foreach ($elements as $element){
					if ($element['name'] == $facet['name']){
						$solrFacets[] = $element['id'] . '_s';
					}
				}
			} else{
				$solrFacets[] = $facet['name'];
			}			
		}
		
		//get highlighting parameters
		$hl = get_option('solr_search_hl');
		$snippets = get_option('solr_search_snippets');
		$fragsize = get_option('solr_search_fragsize');
		$facetSort = get_option('solr_search_facet_sort');
		
		//get sort parameter
		$sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : '';
		
		//if there are facets selected, pass them to Solr
		if (!empty($solrFacets)){
			$additionalParams = array('fl'=>$displayFields, 'facet'=>'true', 'facet.mincount'=>1, 'facet.limit'=>SOLR_FACET_LIMIT, 'facet.field'=>$solrFacets, 'hl'=>$hl, 'hl.snippets'=>$snippets, 'hl.fragsize'=>$fragsize, 'sort'=>$sort, 'facet.sort'=>$facetSort);
			$results = $solr->search($query, $start, $rows, $additionalParams);
		}
		//do no pass facets
		else{
			$additionalParams = array('fl'=>$displayFields, 'sort'=>$sort);
			$results = $solr->search($query, $start, $rows, $additionalParams);
		}
		
		$numFound = $results->response->numFound;
	
		$paginationUrl = $this->getRequest()->getBaseUrl() . '/results/';
	
		$pagination = array(	'page'          => $page, 
								'per_page'      => $rows, 
								'total_results' => $numFound,
								'link'          => $paginationUrl);
	
		Zend_Registry::set('pagination', $pagination);
		//Zend_Registry::set('total_results', $numFound);
	    	
		$this->view->assign(array('results'=>$results, 'pagination'=>$pagination, 'page'=>$page));
		
		//$this->view->displayFields = $displayFields;
		$this->view->facets = $solrFacets;
	}	
	
	//get the displayable fields from the Solr table, which is passed to the view to restrict which fields appear in the results
	private function getDisplayableFields(){
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


