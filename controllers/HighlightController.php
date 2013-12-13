<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_HighlightController extends Omeka_Controller_AbstractActionController
{

    /**
     * Display the "Hit Highlighting Options" form.
     */
    public function indexAction()
    {

        $form = new HighlightForm();

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {

                $values = $form->getValues();

                set_option(
                    'solr_search_hl',
                    $values['solr_search_hl']
                );

                set_option(
                    'solr_search_snippets',
                    $values['solr_search_snippets']
                );

                set_option(
                    'solr_search_fragsize',
                    $values['solr_search_fragsize']
                );

                $this->_helper->flashMessenger(
                    __('Highlighting options successfully saved!'), 'success'
                );
            }
        }	

        $this->view->form = $form;

    }

}
