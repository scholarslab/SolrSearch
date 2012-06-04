<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Configuration controller.
 *
 */

require_once SOLR_SEARCH_PLUGIN_DIR . '/forms/FacetForm.php';

class SolrSearch_ConfigController extends Omeka_Controller_Action
{

    /**
     * Show the facets form.
     *
     * @return void
     */
    public function indexAction()
    {

        // Construct facet form.
        $form = new FacetForm;

        // If the form has been posted.
        if ($this->_request->isPost()) {

            // Validate form.
            if ($form->isValid($this->_request->getPost())) {
                $db = get_db();
                $uploadedData = $form->getValues();

                // Walk the fields.
                foreach ($uploadedData as $k => $values) {

                    if ($k != 'submit') {

                        $split = explode('_', $k);
                        $options = array();

                        /**
                         * Test for is_facet values.
                         */

                        // is_displayed
                        if (isset($values) && in_array('is_displayed', $values)) {
                            $options['is_displayed'] = 1;
                        } else {
                            $options['is_displayed'] = 0;
                        }

                        // is_facet
                        if (isset($values) && in_array('is_facet', $values)) {
                            $options['is_facet'] = 1;
                        } else {
                            $options['is_facet'] = 0;
                        }

                        $data = array(
                            'id'           => $split[1],
                            'is_displayed' => $options['is_displayed'],
                            'is_facet'     => $options['is_facet']
                        );

                        try {
                            $db->insert('solr_search_facets', $data); 
                        } catch (Exception $err) {
                            $this->flashError($err->getMessage());
                            return;
                        }

                    }

                }

                $this->flashSuccess('Solr configuration updated. Be sure to reindex.');
                $this->_redirect('solr-search/config');
            }

        }

        // Push form to view.
        $this->view->form = $form;

    }

}
