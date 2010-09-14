<?php
class SolrSearch_HighlightController extends Omeka_Controller_Action
{
	public function indexAction()
	{
		$form = $this->highlightForm();
		$this->view->form = $form;
	}
	
	public function updateAction()
	{
		$form = $this->highlightForm();
		
		if ($_POST) {
    		if ($form->isValid($this->_request->getPost())) {    
    			//get posted values		
				$uploadedData = $form->getValues();
				set_option('solr_search_hl', $uploadedData['solr_search_hl']);
				set_option('solr_search_snippets', $uploadedData['solr_search_snippets']);
				set_option('solr_search_fragsize', $uploadedData['solr_search_fragsize']);
				$this->flashSuccess('Hit highlighting features modified.');
    		}
	    	else {
	    			$this->flashError('Failed to gather posted data.');
	    			$this->view->form = $form;
	    	}
    	}	
	}
	
	private function highlightForm(){
		require "Zend/Form/Element.php";
	    $form = new Zend_Form();
		$form->setAction('update');    	
	    $form->setMethod('post');
	    $form->setAttrib('enctype', 'multipart/form-data');
	    
	    //set true or false
		$hl = new Zend_Form_Element_Select ('solr_search_hl');
    	$hl->setLabel('Highlighting:');
		$hl->addMultiOption('true', 'True');
		$hl->addMultiOption('false', 'False'); 
	    $hl->setValue(get_option('solr_search_hl'));
	    $form->addElement($hl);
	    
	    //number of snippets
		$snippets = new Zend_Form_Element_Text ('solr_search_snippets');
	    $snippets->setLabel('Snippets:');
	    $snippets->setValue(get_option('solr_search_snippets'));
	    $snippets->setRequired('true');    
		$snippets->addValidator(new Zend_Validate_Int());
	    $form->addElement($snippets);

		//fragment size
	    $fragsize = new Zend_Form_Element_Text ('solr_search_fragsize');
	    $fragsize->setLabel('Fragment Size:');
	    $fragsize->setValue(get_option('solr_search_fragsize'));
	    $fragsize->setRequired('true');    
	    $fragsize->addValidator(new Zend_Validate_Int());
	    $form->addElement($fragsize);
	    
	    //Submit button
    	$form->addElement('submit','submit');
    	$submitElement=$form->getElement('submit');
    	$submitElement->setLabel('Submit');
	    return $form;
	}
}


