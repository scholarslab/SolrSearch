<?php
/**
 * SolrSearch Omeka Plugin config form.
 *
 * Form for configuring SolrSearch plugin.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package    omeka
 * @subpackage SolrSearch
 * @author     "Scholars Lab"
 * @copyright  2010 The Board and Visitors of the University of Virginia
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @version    $Id$
 * @link       http://www.scholarslab.org
 *
 * PHP version 5
 *
 */
?>

<!-- TODO: Refactor in to admin css -->
<style type="text/css">.zend_form>dd{ margin-bottom:20px;}</style>

<div class="field">
    <h3>Solr Options</h3>
    
    <?php 
        // Fix this...
        //require_once 'Zend/Form/Element.php';
        
        $form = new Zend_Form();
        
        $form->setMethod('post');
        
        $form->addElement(
                'text', 
                'solr_search_server',
                array(
                    'required' => true,
                    'label' => 'Server Host:',
                    'value' => get_option('solr_search_server')
                )
        );
        
        $form->addElement(
                'text', 
                'solr_search_port',
                array(
                    'validators' => array('alnum'),
                    'required' => true,
                    'label' => 'Server Port:',
                    'value' => get_option('solr_search_port')
                    
                )
        );
        
        $form->addElement(
                'text', 
                'solr_search_port',
                array(
                    'validators' => array('alnum'),
                    'required' => true,
                    'label' => 'Server Port:',
                    'value' => get_option('solr_search_port')
                )
        );
        
        $form->addElement(
                'text', 
                'solr_search_core',
                array(
                    'validators' => array('alnum', array('regex', false, '/\/.*\//i')),
                    'required' => true,
                    'label' => 'Solr Core Name:',
                    'value' => get_option('solr_search_core')
                )
        );
        
        $form->addElement(
                'text', 
                'solr_search_rows',
                array(
                    'validators' => array('alnum'),
                    'required' => true,
                    'label' => 'Results Per Page:',
                    'value' => get_option('solr_search_rows')
                )
        );
        
        $form->addElement(
                'select', 
                'solr_search_facet_sort',
                array(
                    'validators' => array('alnum'),
                    'required' => true,
                    'label' => 'Default Sort Order:',
                    'value' => get_option('solr_search_facet_sort')
                )
        );
        
        $form->addElement(
                'text', 
                'solr_search_facet_limit',
                array(
                    'validators' => array('alnum'),
                    'required' => true,
                    'label' => 'Maximum Facet Count:',
                    'value' => get_option('solr_search_facet_limit')
                )
        );
        
        
        $form->addElement('submit', 'Save', array('label' => 'Save'));
        
        
        echo $form;
        
    ?>
    
    
    
    
    <?php //echo solr_search_options(); ?>
</div>
