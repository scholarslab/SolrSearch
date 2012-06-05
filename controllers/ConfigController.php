<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Configuration controller.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package omeka
 * @subpackage SolrSearch
 * @author Scholars' Lab
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @copyright 2010 The Board and Visitors of the University of Virginia
 * @link https://github.com/scholarslab/SolrSearch/
 *
 * PHP version 5
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

                /*
                 * $f = fopen('/vagrant/solr.log', 'w');
                 * fwrite($f, print_r($uploadedData, true));
                 * fclose($f);
                 */

                foreach ($uploadedData['facets'] as $group) {
                    foreach ($group['facets'] as $group) {
                        $options = array(
                            'is_displayed' => 0,
                            'is_facet'     => 0
                        );
                        foreach ($group['options'] as $opt) {
                            $options[$opt] = 1;
                        }

                        $db->insert('solr_search_facets', array(
                            'id'           => $group['facetid'],
                            'label'        => $group['label'],
                            'is_displayed' => $options['is_displayed'],
                            'is_facet'     => $options['is_facet'],
                        ));
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
