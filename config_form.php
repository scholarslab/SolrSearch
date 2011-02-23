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
<style type="text/css">
    dd {margin-bottom:20px;}
    /* .required {color: red}*/
</style>

<div class="field">
    <h3>Solr Options</h3>
    
    <?php 
        // Fix this...        
        echo new Zend_Form_Element_Text('solr_search_server', array(
            'required' => true,
            'label' => 'Server Host:',
            'value' => get_option('solr_search_server')
        ));
        
        echo new Zend_Form_Element_Text('solr_search_port', array(
            'required' => true,
            'validators' => array('alnum'),
            'label' => 'Server Port:',
            'value' => get_option('solr_search_port')
        ));
        
        echo new Zend_Form_Element_Text('solr_search_core', array(
            'required' => true,
            'validators' => array('alnum', array(
                'regex',
                false,
                '/\/.*\//i'
                )
            ),
            'label' => 'Solr Core Name:',
            'value' => get_option('solr_search_core')
        ));
        
        // echo new Zend_Form_Element_Text('solr_search_rows', array(
        //           'required' => true,
        //           'validators' => array('alnum'),
        //           'label' => 'Results Per Page:',
        //           'value' => get_option('solr_search_rows')
        //       ));
        
        $rows = new Zend_Form_Element_Text('solr_search_rows');
        $rows->setLabel('Results Per Page')->setRequired(true);
        $rows->setValue(get_option('solr_search_rows'));
        $rows->setValidators(array('alnum'));
        $rows->addErrorMessage('Results count must be numeric');
        echo $rows;
        
        
        $sort = new Zend_Form_Element_Select('solr_search_facet_sort');
        $sort->setLabel('Default Sort Order:')->setRequired(true);
        $sort->addMultiOption('index', 'Alphabetical');
    	$sort->addMultiOption('count', 'Occurrences');    
        $sort->setValue(get_option('solr_search_facet_sort'));
        echo $sort;
        
        echo new Zend_Form_Element_Text('solr_search_facet_limit', array(
            'required' => true,
            'validators' => array('alnum'),
            'label' => 'Maximum Facet Count:',
            'value' => get_option('solr_search_facet_limit')
        ));
        
    ?>
</div>
