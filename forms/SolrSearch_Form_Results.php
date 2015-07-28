<?php

/**
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_Form_Results extends Omeka_Form
{


    /**
     * Build the "Hit Highlighting" form.
     */
    public function init()
    {

        parent::init();

        // Enable Highlighting:
        $this->addElement('checkbox', 'solr_search_hl', array(
            'label'         => __('Enable Highlighting'),
            'description'   => __('Display snippets with highlighted term matches.'),
            'value'         => get_option('solr_search_hl')
        ));

        // Number of Snippets:
        $this->addElement('text', 'solr_search_hl_snippets', array(
            'label'         => __('Number of Snippets'),
            'description'   => __('The maximum number of snippets to display.'),
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

        // Max Analyzed Chars
        $this->addElement('text', 'solr_search_hl_max_analyzed_chars', array(
            'label' => __('Extent of Document Highlightable'),
            'description' => __('How much of the document can be highlighted, in characters. Occurrences past this point will not be returned in the results highlighting.'),
            'value' => get_option('solr_search_hl_max_analyzed_chars'),
            'required' => true,
            'size' => 10,
            'validators' => array(
                array(
                    'validator' => 'Int',
                    'breakChainOnFailure' => true,
                    'options' => array(
                        'messages' => array(
                            Zend_Validate_Int::NOT_INT => __('Must be an integer.')
                        )
                    )
                )
            )
        ));

        // Facet Ordering:
        $this->addElement('select', 'solr_search_facet_sort', array(
            'label'         => __('Facet Ordering'),
            'description'   => __('The sorting criteria for result facets.'),
            'multiOptions'  => array( 'index' => __('Alphabetical'), 'count' => __('Occurrences')),
            'value'         => get_option('solr_search_facet_sort')
        ));

        // Maximum Facet Count:
        $this->addElement('text', 'solr_search_facet_limit', array(
            'label'         => __('Facet Count'),
            'description'   => __('The maximum number of facets to display.'),
            'value'         => get_option('solr_search_facet_limit'),
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

        // Display Private Items:
        $this->addElement('checkbox', 'solr_search_display_private_items', array(
            'label'         => __('Display private items'),
            'description'   => __('Display private items for all user roles with sufficient permission to view them.'),
            'value'         => get_option('solr_search_display_private_items')
        ));


        // Submit:
        $this->addElement('submit', 'submit', array(
            'label' => __('Save Settings')
        ));

        $this->addDisplayGroup(array(
            'solr_search_hl',
            'solr_search_hl_snippets',
            'solr_search_hl_fragsize',
            'solr_search_facet_sort',
            'solr_search_facet_limit',
            'solr_search_display_private_items'
        ), 'fields');

        $this->addDisplayGroup(array(
            'submit'
        ), 'submit_button');

    }


}
