<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_ConfigController extends Omeka_Controller_AbstractActionController
{

    /**
     * Display the "Field Configuration" form.
     */
    public function indexAction()
    {

        $form = new FacetForm;

        // If the form is being submitted.
        if ($this->_request->isPost()) {

            // Validate form.
            if ($form->isValid($this->_request->getPost())) {
                $db = get_db();

                $uploadedData = $form->getValues();

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

                // Flash success.
                $this->_helper->flashMessenger(
                    __('Solr configuration updated. Be sure to reindex.'),
                    'success'
                );

                // Redisplay the form.
                $this->_redirect('solr-search/config');

            }

        }

        // Display the form.
        $this->view->form = $form;

    }

}
