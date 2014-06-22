<?php

/**
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_Form_Server extends Omeka_Form
{


    /**
     * Build the "Server Configuration" button.
     */
    public function init()
    {

        parent::init();

        // Server Host:
        $this->addElement('text', 'solr_search_host', array(
            'label'         => __('Server Host'),
            'description'   => __('The location of the Solr server.'),
            'value'         => get_option('solr_search_host'),
            'required'      => true,
            'size'          => 40
        ));

        // Server Port:
        $this->addElement('text', 'solr_search_port', array(
            'label'         => __('Server Port'),
            'description'   => __('The port that Solr is listening on.'),
            'value'         => get_option('solr_search_port'),
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

        // Core URL:
        $this->addElement('text', 'solr_search_core', array(
            'label'         => __('Core URL'),
            'description'   => __('The URL of the Solr core to index against.'),
            'value'         => get_option('solr_search_core'),
            'required'      => true,
            'size'          => 40,
            'validators'    => array(
                array('validator' => 'Regex', 'breakChainOnFailure' => true, 'options' =>
                    array(
                        'pattern' => '/\/.*\//i',
                        'messages' => array(
                            Zend_Validate_Regex::NOT_MATCH => __('Invalid core URL.')
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
            'solr_search_host',
            'solr_search_port',
            'solr_search_core'
        ), 'fields');

        $this->addDisplayGroup(array(
            'submit'
        ), 'submit_button');

    }


}
