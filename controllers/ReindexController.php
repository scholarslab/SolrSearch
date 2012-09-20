<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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

                    ProcessDispatcher::startProcess(
                        'SolrSearch_DeleteAll',
                        null,
                        $args
                    );

                    ProcessDispatcher::startProcess(
                        'SolrSearch_IndexAll',
                        null,
                        $args
                    );

                    $this->flashSuccess(__('Reindex process started.'));

                } catch (Exception $err) {
                    $this->flashError($err->getMessage());
                }
            }
        }	
    }

    private function facetForm()
    {
        include "Zend/Form/Element.php";
        $form = new Zend_Form();
        $form->setAction('update');
        $form->setMethod('post');
        $form->setAttrib('enctype', 'multipart/form-data');

        //Submit button
        $form->addElement('submit', 'submit');
        $submitElement=$form->getElement('submit');
        $submitElement->setLabel(__('Clear & Reindex'));
        $submitElement->setAttrib('class', 'btn btn-danger btn-large');

        return $form;
    }
}
