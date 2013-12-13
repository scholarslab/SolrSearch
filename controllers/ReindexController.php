<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_ReindexController extends Omeka_Controller_AbstractActionController
{

    /**
     * Display the "Index Items" form.
     */
    public function indexAction()
    {
        $form = $this->_getReindexForm();
        $this->view->form = $form;
    }

    /**
     * Reindex items when form is submitted.
     */
    public function updateAction()
    {
        $form = $this->_getReindexForm();

        if ($_POST) {
            if ($form->isValid($this->_request->getPost())) {
                try{

                    SolrSearch_IndexHelpers::deleteAll(array());
                    SolrSearch_IndexHelpers::indexAll(array());

                    $this->_helper->flashMessenger(
                        __('Reindexing finished.'),
                        'success'
                    );

                } catch (Exception $err) {
                    $this->_helper->flashMessenger($err->getMessage(), 'error');
                }
            }
        }	
    }

    /**
     * Construct the form.
     *
     * @return Zend_Form
     */
    private function _getReindexForm()
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
