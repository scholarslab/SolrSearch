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
        $form = $this->facetForm();
        $this->view->form = $form;

    }

    /**
     * Process the facets form.
     *
     * @return void
     */
    public function updateAction()
    {

      // Get form instance.
      $form = $this->facetForm();

      // If the form has been posted.
      if ($this->_request->isPost()) {

          // Validate form.
          if ($form->isValid($this->_request->getPost())) {

              // Get form values.
              $uploadedData = $form->getValues();

                // Walk the fields.
                foreach ($uploadedData as $k => $values){

                  if ($k != 'submit') {

                      $split = explode('_', $k);
                      $options = array();

                      /**
                       * Test for is_facet and is_sortable values.
                       */

                      // is_displayed
                      if (isset($values) && in_array('is_displayed',$values)){
                          $options['is_displayed'] = 1;
                      } else {
                          $options['is_displayed'] = 0;
                      }

                      // is_facet
                      if (isset($values) && in_array('is_facet',$values)){
                          $options['is_facet'] = 1;
                      } else {
                          $options['is_facet'] = 0;
                      }

                      // is_sortable
                      if (isset($values) && in_array('is_sortable',$values)){
                          $options['is_sortable'] = 1;
                      } else {
                          $options['is_sortable'] = 0;
                      }

                      $data = array('id'=>$split[1], 'is_displayed'=>$options['is_displayed'], 'is_facet'=>$options['is_facet'], 'is_sortable'=>$options['is_sortable']);

                      try {
                          $db = get_db();
                          $db->insert('solr_search_facets', $data); 
                          $this->flashSuccess('Solr facets updated.');
                      } catch (Exception $err) {
                          $this->flashError($err->getMessage());
                      }

                  }

              }

          }

          // If the form is invalid, redisplay.
          else {
              $this->flashError('Failed to gather posted data.');
              $this->view->form = $form;
          }

        }

    }

    /**
     * Construct the form.
     *
     * @return Zend_Form $form The form.
     */
    private function facetForm() {

        // Initialize form.
        require "Zend/Form/Element.php";
        $form = new Zend_Form();
        $form->setAction('update');
        $form->setMethod('post');
        $form->setAttrib('enctype', 'multipart/form-data');

        // Set form as a table
        $form->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'table')),'Form',));

        // Get fields.
        $fields = $this->getTable('SolrSearchFacet')->findAll();

        // Walk fields.
        foreach ($fields as $field) {

            if ($field['element_set_id'] != NULL) {

                $elementSetName = $this->getTable('ElementSet')->find($field['element_set_id'])->name;
                $mC = new Zend_Form_Element_MultiCheckbox('options_' . $field['id']);
                $mC->setLabel($elementSetName . ': ' . $field['name']);
                $mC->setMultiOptions(array(
                  'is_displayed'=>'Is Displayed',
                  'is_facet'=>'Is Facet',
                  'is_sortable'=>'Is Sortable'
                ));

            }

            else {

                $mC = new Zend_Form_Element_MultiCheckbox('options_' . $field['id']);
                $mC->setLabel(ucwords($field['name']));

                if ($field['name'] == 'image') {
                    $mC->setMultiOptions(array( 'is_displayed'=>'Is Displayed'));
                }

                else {
                    $mC->setMultiOptions(array(
                        'is_displayed' => 'Is Displayed',
                        'is_facet' => 'Is Facet',
                        'is_sortable' => 'Is Sortable'
                    ));
                }

            }

            $values = array();
            if ($field['is_displayed'] == 1) {
              $values[] = 'is_displayed';
            }
            if ($field['is_facet'] == 1) {
              $values[] = 'is_facet';
            }
            if ($field['is_sortable'] == 1) {
              $values[] = 'is_sortable';
            }

            $mC->setValue($values);
            $mC->setDecorators(array('ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
                array('Label', array('tag' => 'td')), array(array('row' => 'HtmlTag'), array('tag' => 'tr')),));

            $form->addElement($mC);

        }

        //Submit button
        $form->addElement('submit','submit');
        $submitElement=$form->getElement('submit');
        $submitElement->setLabel('Submit');
        $submitElement->setDecorators(array('ViewHelper',
            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 2)),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
        ));

        return $form;
  }
}
