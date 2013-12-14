<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_AdminController
    extends Omeka_Controller_AbstractActionController
{

    /**
     * Display the "Field Configuration" form.
     */
    public function fieldsAction()
    {

        $form = new FacetForm;

        // If a valid form was submitted.
        if ($this->_request->isPost() && $form->isValid($_POST)) {

            $uploadedData = $form->getValues();
            $db = get_db();

            // Save the facets.
            foreach ($uploadedData['facets'] as $group) {
                foreach ($group['facets'] as $group) {

                    $options = array(
                        'is_displayed' => 0,
                        'is_facet'     => 0
                    );

                    foreach ($group['options'] as $opt) {
                        $options[$opt] = 1;
                    }

                    $db->insert( 'solr_search_facets', array(
                        'id'           => $group['facetid'],
                        'label'        => $group['label'],
                        'is_displayed' => $options['is_displayed'],
                        'is_facet'     => $options['is_facet'],
                    ));

                }
            }

            // Flash success.
            $this->_helper->flashMessenger(
                __('Fields configuration updated. Be sure to reindex.'),
                'success'
            );

        }

        $this->view->form = $form;

    }

    /**
     * Display the "Hit Highlighting Options" form.
     */
    public function highlightingAction()
    {

        $form = new HighlightForm();

        // If a valid form was submitted.
        if ($this->_request->isPost() && $form->isValid($_POST)) {

            // Set options.
            $v= $form->getValues();
            set_option('solr_search_hl',        $v['solr_search_hl']);
            set_option('solr_search_snippets',  $v['solr_search_snippets']);
            set_option('solr_search_fragsize',  $v['solr_search_fragsize']);

            // Flash success.
            $this->_helper->flashMessenger(
                __('Highlighting options successfully saved!'), 'success'
            );

        }	

        $this->view->form = $form;

    }

    /**
     * Display the "Index Items" form.
     */
    public function reindexAction()
    {

        $form = new ReindexForm();

        if ($this->_request->isPost()) {
            try {

                // Clear and reindex.
                SolrSearch_IndexHelpers::deleteAll(array());
                SolrSearch_IndexHelpers::indexAll(array());

                // Flash success.
                $this->_helper->flashMessenger(
                    __('Reindexing finished.'), 'success'
                );

            } catch (Exception $err) {

                // Flash error.
                $this->_helper->flashMessenger(
                    $err->getMessage(), 'error'
                );

            }
        }	

        $this->view->form = $form;

    }

}
