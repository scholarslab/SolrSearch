<?php
class SolrSearch_ConfigController extends Omeka_Controller_Action
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
				foreach ($uploadedData as $k => $values){
					if ($k != 'submit'){
						$split = explode('_', $k);
						
						//test whether or not is_facet and is_sortable have values
						$options = array();
						if (isset($values) && in_array('is_displayed',$values)){
							$options['is_displayed'] = 1;
						} else{
							$options['is_displayed'] = 0;
						}
						if (isset($values) && in_array('is_facet',$values)){
							$options['is_facet'] = 1;
						} else{
							$options['is_facet'] = 0;
						}
						if (isset($values) && in_array('is_sortable',$values)){
							$options['is_sortable'] = 1;
						} else{
							$options['is_sortable'] = 0;
						}
						
						$data = array('id'=>$split[1], 'is_displayed'=>$options['is_displayed'], 'is_facet'=>$options['is_facet'], 'is_sortable'=>$options['is_sortable']);
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

	private function facetForm() {
		    require "Zend/Form/Element.php";
	    	$form = new Zend_Form();
			$form->setAction('update');    	
	    	$form->setMethod('post');
	    	$form->setAttrib('enctype', 'multipart/form-data');
	    	
	    	//set form as a table
	    	$form->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'table')),'Form',));	    	
	    	
	    	$db = get_db();
	    	$fields = $db->getTable('SolrSearch_Facet')->findAll();
	    	
			foreach ($fields as $field){	    		
				if ($field['element_set_id'] != NULL){
	    	   		$elementSetName = $db->getTable('ElementSet')->find($field['element_set_id'])->name;
		    		$mC = new Zend_Form_Element_MultiCheckbox('options_' . $field['id']);
		    		$mC->setLabel($elementSetName . ': ' . $field['name']);
		    		$mC->setMultiOptions(array(	'is_displayed'=>'Is Displayed',
		    									'is_facet'=>'Is Facet', 
	                                			'is_sortable'=>'Is Sortable'));
    			} else {
	    			$mC = new Zend_Form_Element_MultiCheckbox('options_' . $field['id']);
		    		$mC->setLabel(ucwords($field['name']));
		    		if ($field['name'] == 'image'){
		    			$mC->setMultiOptions(array(	'is_displayed'=>'Is Displayed'));
		    		} else{
    		    		$mC->setMultiOptions(array(	'is_displayed'=>'Is Displayed',
    							'is_facet'=>'Is Facet', 
                                'is_sortable'=>'Is Sortable'));
		    		}

    			}
	    		//see if it is checked
	    		$values = array();
				if ($field['is_displayed'] == 1){
	    			$values[] = 'is_displayed';
	    		}
				if ($field['is_facet'] == 1){
	    			$values[] = 'is_facet';
	    		}
				if ($field['is_sortable'] == 1){
	    			$values[] = 'is_sortable';
	    		}
	    		$mC->setValue($values);	    		
	    		$mC->setDecorators(array('ViewHelper',
				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
				array('Label', array('tag' => 'td')), array(array('row' => 'HtmlTag'), array('tag' => 'tr')),));	
	    		$form->addElement($mC);	    		
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


