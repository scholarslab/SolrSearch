<?php
class SolrSearch_ReindexController extends Omeka_Controller_Action
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
    			try{
					ProcessDispatcher::startProcess('SolrSearch_IndexAll', null, $args);
					$this->flashSuccess('Reindex process started.');
				} catch (Exception $err) {
					$this->flashError($err->getMessage());
        		}    			
    		}
    	}	
	}
	
	private function facetForm() {
		    require "Zend/Form/Element.php";
	    	$form = new Zend_Form();
			$form->setAction('update');    	
	    	$form->setMethod('post');
	    	$form->setAttrib('enctype', 'multipart/form-data');
	    	
			//Submit button
	    	$form->addElement('submit','submit');
	    	$submitElement=$form->getElement('submit');
	    	$submitElement->setLabel('Reindex');
	    	
	    	return $form;
	}
}
