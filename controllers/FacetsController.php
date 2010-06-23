<?php
class SolrSearch_FacetsController extends Omeka_Controller_Action
{
	public function indexAction()
	{
		$form = $this->facetForm();
		$this->view->form = $form;
	}
	
	public function updateAction()
	{
		$form = $this->facetForm();
		
    	if ($_POST) {
    		if ($form->isValid($this->_request->getPost())) {    
    			//get posted values		
				$uploadedData = $form->getValues();
				
				//cycle through each checkbox
				foreach ($uploadedData as $k => $v){
					if ($k != 'submit'){
						$split = explode('_', $k);
						$data = array('id'=>$split[0], 'is_facet'=>$v);
						try{
							//update the database with new values
							$db = get_db();
							$db->insert('solr_search_facets', $data); 
							$this->flashSuccess('Solr facets updated.');
						} catch (Exception $err) {
							$this->flashError($err->getMessage());
        				}
					}		
				}
    		}
	    	else {
	    			$this->flashError('Failed to gather posted data.');
	    			$this->view->form = $form;
	    	}
    	}    	
	}
	
	private function facetForm()
		{
		    require "Zend/Form/Element.php";
	    	$form = new Zend_Form();
			$form->setAction('update');    	
	    	$form->setMethod('post');
	    	$form->setAttrib('enctype', 'multipart/form-data');
	    	
	    	//set form as a table
	    	$form->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'table')),'Form',));	    	
	    	
	    	$db = get_db();
	    	$facets = $db->getTable('SolrSearch_Facet')->findAll();
	    	
	    	foreach ($facets as $facet){	    		
	    		$elementSet = $db->getTable('ElementSet')->find($facet['element_set_id']);
	    		$elementSetName = $elementSet['name'];
	    		$facetName = new Zend_Form_Element_Checkbox($facet['id'] . '_facetCheckbox');
	    		$facetName->setLabel($elementSetName . ': ' . $facet['name']);
	    		if ($facet['is_facet'] == 1){
	    			$facetName->setCheckedValue(true)
	    				->setValue(true);
	    		}
	    		
	    		//set each element as a table row
	    		$facetName->setDecorators(array('ViewHelper',
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')), array(array('row' => 'HtmlTag'), array('tag' => 'tr')),));	
	    		$form->addElement($facetName);
	    	}    	
	    	
			//Submit button
	    	$form->addElement('submit','submit');
	    	$submitElement=$form->getElement('submit');
	    	$submitElement->setLabel('Submit');
	    	$submitElement->setDecorators(array('ViewHelper',
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 2)),
				 array(array('row' => 'HtmlTag'), array('tag' => 'tr')),));	
	    	
	    	return $form;
	}
}


