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
?><?php

/**
 * This is a collection of utilities to make displaying results and generating 
 * URLs easier.
 **/
class SolrSearch_ViewHelpers
{
    /**
     * This returns the base URL for the results page.
     *
     * @return string
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function getBaseUrl()
    {
        return uri('/solr-search/results/');
    }

    /**
     * This creates the default search form.
     *
     * @param string $buttonText      The text to use on the button.
     * @param array  $formProperties  Extra HTML attributes to add to the
     *                                form element.
     *
     * @return string
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function createSearchForm(
        $buttonText='Search', $formProperties=array( 'id' => 'simple-search' )
    ) {
        $uri = SolrSearch_ViewHelpers::getBaseUrl();
        $formProperties['action'] = $uri;
        $formProperties['method'] = 'get';
        $html  = '<form ' . _tag_attributes($formProperties) . '>' . "\n";
        $html .= '<fieldset>' . "\n\n";
        $html .= __v()->formText('solrq', '', array('name'=>'solrq','class'=>'textinput'));
        $html .= __v()->formHidden('solrfacet', '');
        $html .= __v()->formSubmit('submit_search', $buttonText);
        $html .= '</fieldset>' . "\n\n";
        $html .= '</form>';
        return $html;
    }

    /**
     * Looks up element name for all Solr elements.
     *
     * @return array An array indexed by element IDs, mapping to element names.
     */
    public static function getElementNames()
    {
        $index = array();

        $db = get_db();
        $select = $db
            ->select()
            ->from("{$db->prefix}elements", array('id', 'name'));
        $stmt   = $select->query();
        $result = $stmt->fetchAll();

        foreach ($result as $row) {
            $index['' . $row['id']] = $row['name'];
        }

        return $index;
    }

    /**
     * Looks up a Solr element name (ID_suffix) in the index returned by 
     * solr_search_get_element_names.
     *
     * @param array  $index The index returned by solr_search_get_element_names.
     * @param string $name  The Solr element name.
     *
     * @return string The display label for the element.
     */
    public static function getElementName($index, $name)
    {
        $field = explode('_', $name);
        $id    = $field[0];
        return $index[$id];
    }

    /**
     *
     * Lookup the element name for a solr element
     *
     * TODO: store this in the solr index (add sub-index field for this)
     *
     * @param type $field
     * @return type
     */
    public static function lookupElement($field)
    {
        return $field;
        /*
         * $fieldarray = explode('_', $field);
         * $fieldId    = $fieldarray[0];
         * $db         = get_db();
         * $element    = $db->getTable('Element')->find($fieldId);
         * return $element['name'];
         */
    }

    /**
     * Generate a Results link for SolrSearch
     *
     * @param type $doc
     * @return type
     */
    public static function createResultLink($doc)
    {
        $title = SolrSearch_ViewHelpers::getDocTitle($doc);
        $uri   = html_escape(WEB_ROOT) . '/items/show/';
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
    public static function getDocTitle($doc)
    {
        $db    = get_db();
        $item  = $db->getTable('Item')->find($doc->id);
        $title = strip_formatting(item('Dublin Core', 'Title', $options, $item));
        return $title;
    }

    /**
     * Return the current search URL with only the given facet removed.
     *
     * @param string $facet The facet to remove.
     * @param string $label The facet label (value) to remove.
     *
     * @return string The current search URL without the given facet.
     */
    public static function removeFacet($facet, $label)
    {
        // Deconstruct current query and remove particular facet.
        $queryParams = SolrSearch_QueryHelpers::getParams();
        $newParams = array();
        $removeFacetLink = "[<a href='$uri?";
        $query = array();

        if (isset($queryParams['q'])) {
            array_push($query, "solrq={$queryParams['q']}");
        }

        $queryParams = explode(' AND ', $_REQUEST['q']);
        if (isset($queryParams['facet'])) {
            $facetKey = "$facet:\"$label\"";
            $facetQuery = array();
            foreach (explode(' AND ', $queryParams['facet']) as $value) {
                if ($value != $facetKey) {
                    array_push($facetQuery, $value);
                }
            }
            if (!empty($facetQuery)) {
                array_push($query, implode('+AND+', $facetQuery));
            }
        }

        if (empty($query)) {
            array_push($query, html_escape('solrq=*:*'));
        }

        $removeFacetLink = "[<a href='$uri?" . implode('&', $query) . '\'>X</a>]';
        return $removeFacetLink;
    }

    /**
     *
     * @return Zend_Form
     */
    function createSortForm()
    {
        $params = SolrSearch_QueryHelpers::getParams();
        $uri = SolrSearch_ViewHelpers::getBaseUrl();
        require "Zend/Form/Element.php";

        $form = new Zend_Form();
        $form->setAction($uri);
        $form->setMethod('get');
        $form->setDecorators(array('FormElements',array('HtmlTag', array('tag' => 'div')),'Form',));

        $query = new Zend_Form_Element_Hidden('solrq');
        $query->setValue($params['q']);
        $query->setDecorators(
                array('ViewHelper',
                    array(array('data' => 'HtmlTag'),
                    array('tag' => 'span', 'class' => 'element')),
                    array('Label', array('tag' => 'span')),));
        $form->addElement($query);

        $facet = new Zend_Form_Element_Hidden('solrfacet');
        $facet->setValue($params['facet']);
        $facet->setDecorators(
                array('ViewHelper',
                    array(array('data' => 'HtmlTag'),
                    array('tag' => 'span', 'class' => 'element')),
                    array('Label', array('tag' => 'span')),));
        $form->addElement($facet);

        $sortField = new Zend_Form_Element_Select('sort');
        $sortField->setLabel('Sorted By:');

        //get sortable fields
        $db = get_db();
        $sortableList = $db
            ->getTable('SolrSearch_Facet')
            ->findBySql('is_sortable = ?', array('1'));

        //sortable fields
        $fields = array();
        $fields[''] = 'Relevancy';
        foreach ($sortableList as $sortable) {
            if ($sortable->element_id != NULL) {
                $elements = $db
                    ->getTable('Element')
                    ->findBySql(
                        'element_set_id = ?',
                        array($sortable['element_set_id'])
                    );

                foreach ($elements as $element) {
                    if ($element['name'] == $sortable['name']){
                        $fields[$element->id . '_s asc'] = $element->name
                            . ', Ascending';
                        $fields[$element->id . '_s desc'] = $element->name
                            . ', Descending';
                    }
                }
            } else {
                $fields[$sortable->name . ' asc'] = ucwords($sortable->name)
                    . ', Ascending';
                $fields[$sortable->name . ' desc'] = ucwords($sortable->name)
                    . ', Descending';
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


        // Only return the form if there are sortable fields (other than 
        // relevancy).
        if (count($fields) > 1) {
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
    public static function getImagePath($type='fullsize', $fileId)
    {
        $db   = get_db();
        $file = $db->getTable('File')->find($fileId);
        return $file->getWebPath($type);
    }

    /**
     * Display a search snippet if enabled
     *
     * @param type $id
     * @param type $highlighting
     */
    public static function displaySnippets($id, $highlighting)
    {
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

    /**
     * This takes a keyed array of query parameters and returns an array with the
     * values to pass to Solr.
     *
     * @param array  $query   This is the array of parameters passed as GET or POST
     * parameters.
     * @param string $default This is the value of 'q' to use if one isn't
     * provided. This defaults to '*:*'.
     *
     * @return string The 'q' parameter to pass to the Solr engine.
     */
    public static function createQuery($query, $default='*:*')
    {
        $q = (isset($query['q']) && strlen($query['q']) > 0) ?
            $query['q'] :
            $default;

        if (isset($query['facet']) && strlen($query['facet']) > 0) {
            $q .= " AND ({$query['facet']})";
        }

        return $q;
    }

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
?>
