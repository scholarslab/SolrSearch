<?php

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
     * Display the "Server Configuration" form.
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
     * Display the "Collection Configuration" form.
     *
     * @return void
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function collectionsAction()
    {
        if ($this->_request->isPost()) {
            $this->_updateCollections($this->_request->getPost());
        }
        $this->view->form = $this->_collectionsForm();
    }

    /**
     * This updates the excluded collections.
     *
     * @param array $post The post data to update from.
     * @return void
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    protected function _updateCollections($post)
    {
        $etable = $this->_helper->db->getTable('SolrSearchExclude');
        $etable->query("DELETE FROM {$etable->getTableName()};");

        $c = 0;
        if (isset($post['solrexclude'])) {
            foreach ($post['solrexclude'] as $exc) {
                $exclude = new SolrSearchExclude();
                $exclude->collection_id = $exc;
                $exclude->save();
                $c += 1;
            }
        }

        $this->_helper->_flashMessenger("$c collection(s) excluded.");
    }

    /**
     * This returns the form for the collections.
     *
     * @return Zend_Form
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    protected function _collectionsForm()
    {
        $ctable      = $this->_helper->db->getTable('Collection');
        $private     = (int)get_option('solr_search_display_private_items');

        if ($private) {
            $collections = $ctable->findAll();
        } else {
            $collections = $ctable->findBy(array('public' => 1));
        }

        $form = new Zend_Form();
        $form->setAction(url('solr-search/collections'))->setMethod('post');

        $collbox = new Zend_Form_Element_MultiCheckbox('solrexclude');
        $form->addElement($collbox);
        foreach ($collections as $c) {
            $title = metadata($c, array('Dublin Core', 'Title'));
            $collbox->addMultiOption("{$c->id}", $title);
        }

        $etable   = $this->_helper->db->getTable('SolrSearchExclude');
        $excludes = array();
        foreach ($etable->findAll() as $exclude) {
            $excludes[] = "{$exclude->collection_id}";
        }
        $collbox->setValue($excludes);

        $form->addElement('submit', 'Exclude');

        return $form;
    }

    /**
     * Update the set of fields in the facet set.
     *
     * @author Eric Rochester <erochest@virginia.edu>
     */
    public function updatefacetAction()
    {
        $fieldTable = $this->_helper->db->getTable('SolrSearchField');
        $fieldTable->updateFacetMappings();
        $this->redirect('solr-search/fields');
    }

    /**
     * Display the "Field Configuration" form.
     */
    public function fieldsAction()
    {

        // Get the facet mapping table.
        $fieldTable = $this->_helper->db->getTable('SolrSearchField');

        // If the form was submitted.
        if ($this->_request->isPost()) {

            // Gather the POST arguments.
            $post = $this->_request->getPost();

            // Save the facets.
            foreach ($post['facets'] as $name => $data) {

                // Were "Is Indexed?" and "Is Facet?" checked?
                $indexed = array_key_exists('is_indexed', $data) ? 1 : 0;
                $faceted = array_key_exists('is_facet', $data) ? 1 : 0;

                // Load the facet mapping.
                $facet = $fieldTable->findBySlug($name);

                // Apply the updated values.
                $facet->label       = $data['label'];
                $facet->is_indexed  = $indexed;
                $facet->is_facet    = $faceted;
                $facet->save();

            }

            // Flash success.
            $this->_helper->flashMessenger(
                __('Fields successfully updated! Be sure to reindex.'),
                'success'
            );

        }

        // Assign the facet groups.
        $this->view->groups = $fieldTable->groupByElementSet();

    }


    /**
     * Display the "Results Configuration" form.
     */
    public function resultsAction()
    {

        $form = new SolrSearch_Form_Results();

        // If a valid form was submitted.
        if ($this->_request->isPost() && $form->isValid($_POST)) {

            // Set the options.
            foreach ($form->getValues() as $option => $value) {
                set_option($option, $value);
            }

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
                Zend_Registry::get('job_dispatcher')->sendLongRunning(
                    'SolrSearch_Job_Reindex'
                );

                // Flash success.
                $this->_helper->flashMessenger(
                    __('Reindexing started.'), 'success'
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
