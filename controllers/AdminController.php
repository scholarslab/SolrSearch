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

        // If the form was submitted.
        if ($this->_request->isPost()) {

            // Save the facets.
            foreach ($this->_request->getPost()['facets'] as $facet) {

                $isDisplayed        = 0;
                $isFacet            = 0;

                if (array_key_exists('options', $facet)) {
                    $options        = $facet['options'];
                    $isDisplayed    = in_array('is_displayed', $options);
                    $isFacet        = in_array('is_facet', $options);
                }

                // Insert or update the rows.
                get_db()->insert('solr_search_facets', array(
                    'id'            => $facet['id'],
                    'label'         => $facet['label'],
                    'is_displayed'  => $isDisplayed,
                    'is_facet'      => $isFacet
                ));

            }

            // Flash success.
            $this->_helper->flashMessenger(
                __('Fields successfully updated! Be sure to reindex.'),
                'success'
            );

        }

        // Assign the facet groups.
        $facets = $this->_helper->db->getTable('SolrSearchFacet');
        $this->view->groups = $facets->groupByElementSet();

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
            set_option('solr_search_hl_length', $v['solr_search_hl_length']);
            set_option('solr_search_hl_count',  $v['solr_search_hl_count']);

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
