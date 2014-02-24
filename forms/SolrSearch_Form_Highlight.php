<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_Form_Highlight extends Omeka_Form
{


    /**
     * Build the "Hit Highlighting" form.
     */
    public function init()
    {

        parent::init();

        // Enable Highlighting:
        $this->addElement('select', 'solr_search_hl', array(
            'label'         => __('Enable Highlighting'),
            'description'   => __('Enable/Disable highlighting matches in Solr fields.'),
            'multiOptions'  => array( 'true' => __('True'), 'false' => __('False')),
            'value'         => get_option('solr_search_hl')
        ));

        // Number of Snippets:
        $this->addElement('text', 'solr_search_hl_snippets', array(
            'label'         => __('Number of Snippets'),
            'description'   => __('The maximum number of highlighted snippets to generate.'),
            'value'         => get_option('solr_search_hl_snippets'),
            'required'      => true,
            'size'          => 40,
            'validators'    => array(
                array('validator' => 'Int', 'breakChainOnFailure' => true, 'options' =>
                    array(
                        'messages' => array(
                            Zend_Validate_Int::NOT_INT => __('Must be an integer.')
                        )
                    )
                )
            )
        ));

        // Snippet Length:
        $this->addElement('text', 'solr_search_hl_fragsize', array(
            'label'         => __('Snippet Length'),
            'description'   => __('The maximum number of characters to display in a snippet.'),
            'value'         => get_option('solr_search_hl_fragsize'),
            'required'      => true,
            'size'          => 40,
            'validators'    => array(
                array('validator' => 'Int', 'breakChainOnFailure' => true, 'options' =>
                    array(
                        'messages' => array(
                            Zend_Validate_Int::NOT_INT => __('Must be an integer.')
                        )
                    )
                )
            )
        ));

        // Submit:
        $this->addElement('submit', 'submit', array(
            'label' => __('Save Settings')
        ));

        $this->addDisplayGroup(array(
            'solr_search_hl',
            'solr_search_hl_snippets',
            'solr_search_hl_fragsize'
        ), 'fields');

        $this->addDisplayGroup(array(
            'submit'
        ), 'submit_button');

    }


}
