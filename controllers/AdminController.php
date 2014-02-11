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
     * Display the "Solr Configuration" form.
     */
    public function serverAction()
    {

        $form = new SolrSearch_Form_Server();

        // If a valid form was submitted.
        if ($this->_request->isPost() && $form->isValid($_POST)) {

            // Set the options.
            foreach ($form->getValues() as $option => $value) {
                set_option($option, $value);
            }

        }

        // Are the current parameters valid?
        if (SolrSearch_Helpers_Index::pingSolrServer()) {

            // Notify valid connection.
            $this->_helper->flashMessenger(
                __('Solr connection is valid.'), 'success'
            );

        }

        // Notify invalid connection.
        else $this->_helper->flashMessenger(
            __('Solr connection is invalid.'), 'error'
        );

        $this->view->form = $form;

    }

    /**
     * Display the "Field Configuration" form.
     */
    public function fieldsAction()
    {

        $form = new SolrSearch_Form_Fields();

        // If a valid form was submitted.
        if ($this->_request->isPost() && $form->isValid($_POST)) {

            // Save the facets.
            foreach ($form->getValues() as $group) {
                foreach ($group as $facet) {

                    $options = array(
                        'is_displayed'  => 0,
                        'is_facet'      => 0
                    );

                    // Flip on the activated options.
                    if (is_array($facet['options'])) {
                        foreach ($facet['options'] as $opt) {
                            $options[$opt] = 1;
                        }
                    }

                    // Insert or update the rows.
                    get_db()->insert('solr_search_facets', array(
                        'id'            => $facet['facetid'],
                        'label'         => $facet['label'],
                        'is_displayed'  => $options['is_displayed'],
                        'is_facet'      => $options['is_facet']
                    ));

                }
            }

            // Flash success.
            $this->_helper->flashMessenger(
                __('Fields successfully updated! Be sure to reindex.'),
                'success'
            );

        }

        $this->view->form = $form;

    }

    /**
     * Display the "Hit Highlighting Options" form.
     */
    public function highlightAction()
    {

        $form = new SolrSearch_Form_Highlight();

        // If a valid form was submitted.
        if ($this->_request->isPost() && $form->isValid($_POST)) {

            // Set options.
            $v = $form->getValues();
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

        $form = new SolrSearch_Form_Reindex();

        if ($this->_request->isPost()) {
            try {

                // Clear and reindex.
                SolrSearch_Helpers_Index::deleteAll();
                SolrSearch_Helpers_Index::indexAll();

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
