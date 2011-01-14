<?php
/**
 * SolrSearch Omeka Plugin helpers.
 *
 * Default helpers for the SolrSearch plugin
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

<?php
/**
 * Create a default search form
 * 
 * @param type $buttonText
 * @param type $formProperties
 * @return string 
 */
function solr_search_form($buttonText = "Search", $formProperties=array('id'=>'simple-search')) 
{ 
    $uri = WEB_ROOT . '/solr-search/results/';
    $formProperties['action'] = $uri;
    $formProperties['method'] = 'get';
    $html  = '<form ' . _tag_attributes($formProperties) . '>' . "\n";
    $html .= '<fieldset>' . "\n\n";
    $html .= __v()->formText('q', '', array('name'=>'textinput','class'=>'textinput'));
    $html .= __v()->formSubmit('submit_search', $buttonText);
    $html .= '</fieldset>' . "\n\n";
    $html .= '</form>';
    return $html;
}

/**
 *
 * Lookup the element name for a solr element
 * 
 * TODO: store this in the solr index
 * 
 * @param type $field
 * @return type 
 */
function solr_search_element_lookup($field){
    $fieldarray = explode('_', $field);
    $fieldId = $fieldarray[0];
    $db = get_db();
    $element = $db->getTable('Element')->find($fieldId);
    return $element['name'];
}

/**
 * Generate a Results link for SolrSearch
 * 
 * @param type $doc
 * @return type 
 */
function solr_search_result_link($doc){
    //get title of doc
    $title = solr_search_doc_title($doc);
	
    //generate link to item
    $uri = html_escape(WEB_ROOT) . '/items/show/';
    return '<a href="' . $uri . $doc->id .'">' . $title . '</a>';
}

/**
 * Return the title of doc
 * 
 * TODO: limit DB lookups
 * 
 * @param type $doc
 * @return string Title of the item
 */
function solr_search_doc_title($doc){
    $db = get_db();
    $item = $db->getTable('Item')->find($doc->id);
    $title = strip_formatting(item('Dublin Core', 'Title', $options, $item));
    return $title;
}

/**
 * Create a SolrFacetLink
 * 
 * @param type $facet
 * @param type $label
 * @param type $count
 * @return string 
 */
function solr_search_facet_link($facet,$label,$count){
    $uri = html_escape(WEB_ROOT) . '/solr-search/results/';
	//if the query contains one of the facets in the list
    
    //TODO: refactor
    if(strstr($_REQUEST['q'], $facet . ':"' . $label . '"')) {
        //generate remove facet link
        $removeFacetLink = solr_search_remove_facet($facet,$label);		
	
        $html .= '<div class="fn"><b>' . $label . '</b></div>';
        $html .= '<div class="fc">' . $removeFacetLink . '</div>';
        
        return $html;
    } else{
      //otherwise just display a link to a new query with the facet count
        $html .= "<div class='fn'><a href='" . $uri . '?q=' . html_escape($_REQUEST['q']) . ' AND ' . $facet . ':&#x022;' . $label ."&#x022;'>" . $label . '</a></div>';
        $html .= '<div class="fc">' . $count . '</div>';
	return $html;
    }
    
    return $html;
}

/**
 * Create a new anchor with a field popped 
 * 
 * @return string 
 */
function solr_search_remove_facets()
{
    $uri = html_escape(WEB_ROOT) . '/solr-search/results/';
    $queryParams = explode(' AND ', $_REQUEST['q']);
	
    //if there is only one tokenized string in the query and that string is *:*, return ALL TERMS text
    if ($queryParams[0] == end($queryParams) && $queryParams[0] == '*:*'){
        $html = '<li><b>ALL TERMS</b></li>';
    } else { //otherwise continue with process of displaying facets and removal links
        
        foreach ($queryParams as $param){
            $paramSplit = explode(':', $param);
            
            if ($paramSplit[1] != NULL){
                $facet = $paramSplit[0];
                $label = str_replace('"', '', $paramSplit[1]);
                
                if (strstr($param, '_')) { 
                    $category = solr_search_element_lookup($facet);     
                } else { 
                    $category = ucwords($facet); 
                    
                }	
                
                if ($facet != '*'){
                    $html .= '<li><b>' . $category . ':</b> ';
                    $html .= $label . ' ' . solr_search_remove_facet($facet,$label) . '</li>';
                }
                
            } else {
                $html .= '<li><b>Keyword:</b> ' . $param . ' [<a href="' . $uri . '?q='. str_replace($param, '*:*', html_escape($_REQUEST['q'])) .'">X</a>]</li>';
            }
        }	
    }

    return $html;
}

function solr_search_remove_facet($facet,$label){
	//deconstruct current query and remove particular facet
	$queryParams = explode(' AND ', $_REQUEST['q']);
	$newParams= array();
	$removeFacetLink = "[<a href='" . $uri . '?q=';		
	foreach ($queryParams as $key => $queryParam){
		if($queryParam != $facet . ':"' . $label . '"'){
			$newParams[] = $queryParam;
		}
	}
	//if there is only one query parameter, a facet that has been removed, search everything by default
	if (empty($newParams)){
		$removeFacetLink .= '*:*';
	}
	//build new query
	else {
		$removeFacetLink .= implode(' AND ', $newParams);
	}		
	$removeFacetLink .= "'>X</a>]";
	return $removeFacetLink;
}

/**
 *
 * @return Zend_Form 
 */
function solr_search_sort_form() {
    
    $uri = html_escape(WEB_ROOT) . '/solr-search/results/';
    require "Zend/Form/Element.php";
    
    $form = new Zend_Form();
    $form->setAction($uri);    	
    $form->setMethod('get');
    $form->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div')),'Form',));	
	
    $query = new Zend_Form_Element_Hidden('q');
    $query->setValue($_REQUEST['q']);
    $query->setDecorators(
            array('ViewHelper',
                array(array('data' => 'HtmlTag'), 
                array('tag' => 'span', 'class' => 'element')),
                array('Label', array('tag' => 'span')),));	
    
    $form->addElement($query);
	
    $sortField = new Zend_Form_Element_Select('sort');
    $sortField->setLabel('Sorted By:');
	
    //get sortable fields
    $db = get_db();
    $sortableList = $db->getTable('SolrSearch_Facet')->findBySql('is_sortable = ?', array('1'));

    //sortable fields
    $fields = array();
    $fields[''] = 'Relevancy';
    foreach ($sortableList as $sortable){
        if ($sortable->element_id != NULL){
            $elements = $db->getTable('Element')->findBySql('element_set_id = ?', array($sortable['element_set_id']));

            foreach ($elements as $element){
                if ($element['name'] == $sortable['name']){
                    $fields[$element->id . '_s asc'] = $element->name . ', Ascending';
                    $fields[$element->id . '_s desc'] = $element->name . ', Descending';
		}
            }
	} else {
            $fields[$sortable->name . ' asc'] = ucwords($sortable->name) . ', Ascending';
            $fields[$sortable->name . ' desc'] = ucwords($sortable->name) . ', Descending';
	}

    }
    
    $sortField->setOptions(array('multiOptions'=>$fields));
    $sortField->setDecorators(array('ViewHelper',
        array(
            array('data' => 'HtmlTag'), 
            array('tag' => 'span', 'class' => 'element')),
        array('Label', array('tag' => 'span')),));
				
	//select the current sorted option
    $sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : '';
    $sortField->setValue($sort);				
    $form->addElement($sortField);
	
    //Submit button
    $form->addElement('submit','submit');
    $submitElement=$form->getElement('submit');
    $submitElement->setLabel('Go');
    $submitElement->setDecorators(array(
        'ViewHelper', array(
            array('data' => 'HtmlTag'), 
            array('tag' => 'span', 'class' => 'element')),));		
	
        
    // only return the form if there are sortable fields (other than relevancy)
    if (count($fields) > 1){
        return $form;
    } else {
        return '';
    }
	
}

/**
 * Return the path for an image
 * 
 * @param type $type
 * @param type $fileId
 * @return type 
 */
function solr_search_image_path($type='fullsize', $fileId){
    $db = get_db();	
    $file = $db->getTable('File')->find($fileId);
    return $file->getWebPath($type);
}

/**
 * Display a search snippet if enabled
 * 
 * @param type $id
 * @param type $highlighting 
 */
function solr_search_display_snippets($id, $highlighting){
    foreach ($highlighting as $k=>$v){
        if ($k == $id){
            foreach($v as $k=>$snippets){
                foreach ($snippets as $snippet){
                    echo $snippet;
                    
                    if ($snippet != end($snippets)){
                        echo ' <b>...</b> ';
                    }
                }
            }
        }
    }
}
?>
