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
        $form = $this->_getHighlightForm();
        $this->view->form = $form;
    }

    /**
     * Process the form.
     * TODO: Fold this into the index action.
     */
    public function updateAction()
    {
        $form = $this->_getHighlightForm();

        if ($_POST) {
            if ($form->isValid($this->_request->getPost())) {
                //get posted values		
                $uploadedData = $form->getValues();

                set_option(
                    'solr_search_hl',
                    $uploadedData['solr_search_hl']
                );

                set_option(
                    'solr_search_snippets',
                    $uploadedData['solr_search_snippets']
                );

                set_option(
                    'solr_search_fragsize',
                    $uploadedData['solr_search_fragsize']
                );

                $this->_helper->flashMessenger(__('Hit highlighting features modified.'), 'success');
            } else {
                $this->_helper->flashMessenger(__('Failed to gather posted data.'), 'error');
                $this->view->form = $form;
            }
        }	
    }

    /**
     * Construct the form.
     *
     * @return Zend_Form
     */
    protected function _getHighlightForm()
    {
        include "Zend/Form/Element.php";
        $form = new Zend_Form();
        $form->setAction('update');
        $form->setMethod('post');
        $form->setAttrib('enctype', 'multipart/form-data');

        //set true or false
        $hl = new Zend_Form_Element_Select('solr_search_hl');
        $hl->setLabel(__('Highlighting:'));
        $hl->setDescription(__('Enable/Disable highlighting matches in Solr fields'));
        $hl->addMultiOption('true', __('True'));
        $hl->addMultiOption('false', __('False')); 
        $hl->setValue(get_option('solr_search_hl'));
        $form->addElement($hl);

        //number of snippets
        $snippets = new Zend_Form_Element_Text('solr_search_snippets');
        $snippets->setLabel(__('Snippets:'));
        $snippets->setDescription(__('The maximum number of highlighted snippets to generate'));
        $snippets->setValue(get_option('solr_search_snippets'));
        $snippets->setRequired('true');
        $snippets->addValidator(new Zend_Validate_Int());
        $form->addElement($snippets);

        //fragment size
        $fragsize = new Zend_Form_Element_Text('solr_search_fragsize');
        $fragsize->setLabel(__('Snippet Size:'));
        $fragsize->setDescription(__('The maximum number of characters to display in a snippet'));
        $fragsize->setValue(get_option('solr_search_fragsize'));
        $fragsize->setRequired('true');
        $fragsize->addValidator(new Zend_Validate_Int());
        $form->addElement($fragsize);

        //Submit button
        $form->addElement('submit', 'submit');
        $submitElement=$form->getElement('submit');
        $submitElement->setLabel(__('Submit'));
        return $form;
    }

}
