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

                foreach ($uploadedData['facets'] as $group) {
                    foreach ($group['facets'] as $group) {
                        $options = array(
                            'is_displayed' => 0,
                            'is_facet'     => 0
                        );
                        foreach ($group['options'] as $opt) {
                            $options[$opt] = 1;
                        }

                        $db->insert(
                            'solr_search_facets',
                            array(
                                'id'           => $group['facetid'],
                                'label'        => $group['label'],
                                'is_displayed' => $options['is_displayed'],
                                'is_facet'     => $options['is_facet'],
                            )
                        );
                    }
                }

                $this->flashSuccess(__('Solr configuration updated. Be sure to reindex.'));
                $this->_redirect('solr-search/config');
            }

        }

        // Push form to view.
        $this->view->form = $form;

    }

}
