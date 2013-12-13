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

        $form = new ReindexForm();

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                try{

                    SolrSearch_IndexHelpers::deleteAll(array());
                    SolrSearch_IndexHelpers::indexAll(array());

                    $this->_helper->flashMessenger(
                        __('Reindexing finished.'), 'success'
                    );

                } catch (Exception $err) {
                    $this->_helper->flashMessenger($err->getMessage(), 'error');
                }
            }
        }	

        $this->view->form = $form;

    }

}
