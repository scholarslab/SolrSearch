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
        $searchQuery = array_key_exists('solrq', $_GET) ? $_GET['solrq'] : '';

        $uri = SolrSearch_ViewHelpers::getBaseUrl();
        $formProperties['action'] = $uri;
        $formProperties['method'] = 'get';
        $html  = '<form ' . _tag_attributes($formProperties) . '>' . "\n";
        $html .= '<fieldset>' . "\n\n";
        $html .= __v()->formText('solrq', $searchQuery, array('name'=>'solrq', 'value' => $searchQuery, 'class'=>'textinput'));
        //$html .= __v()->formHidden('solrfacet', '');
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
     * getElementNames.
     *
     * @param array  $index The index returned by getElementNames.
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
     * Lookup the element name for a solr element
     *
     * TODO: store this in the solr index (add sub-index field for this)
     *
     * @param string $field Field name to look up
     * 
     * @return string Human readable solr element name
     */
    public static function lookupElement($field)
    {
        $fieldArray = explode('_', $field);
        $fieldId = $fieldArray[0];
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
    public static function createResultLink($doc)
    {
        return "<a href='{$doc->url}'>{$doc->title}</a>";
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
        $title = null;

        if (isset($doc->title)) {
            $title = $doc->title;
        } else {
            $db    = get_db();
            $item  = $db->getTable('Item')->find($doc->id);
            $title = strip_formatting(item('Dublin Core', 'Title', $options, $item));
        }

        return $title;
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
      if($highlighting == null) { return; }

      foreach ($highlighting as $k=>$v){
            if ($k == $id){
                foreach($v as $k=>$snippets){
                    foreach ($snippets as $snippet){
                        echo $snippet;

                        if ($snippet != end($snippets)){
                            echo ' <strong>...</strong> ';
                        }
                    }
                }
            }
        }
    }

    /**
     * Generate an image tag for use in search results.
     *
     * @param int    $image_id Image to look up
     * @param string $alt      Alt text for image
     * @return string $html link to image
     * @author Wayne Graham <wsg4w@virginia.edu>
     **/
    public static function createResultImgHtml($image_id, $alt)
    {
        $html = '';
        $fullsize_path = SolrSearch_ViewHelpers::getImagePath(
            'fullsize', $image_id
        );
        $thumb_path = SolrSearch_ViewHelpers::getImagePath(
            'square_thumbnail', $image_id
        );

        $html .= '<a class="solr_search_image" href="' . $fullsize_path . '">';
        $html .= '<img alt="' . $alt . '" src="' . $thumb_path . '" />';
        $html .= '</a>';

        return $html;
    }

    /**
     * Output a tag string for a given Solr search result
     *
     * @param array $tags An array of tags to display from a Solr result
     * @param string $delimiter ', ' (comma and whitespace) by default
     *
     * @return string tagString Facet link for given tag
     * @author Wayne Graham <wsg4w@virginia.edu>
     */
    public static function tagsToStrings($tags=array(), $delimiter=null)
    {
        $uri = SolrSearch_ViewHelpers::getBaseUrl();
        $current = SolrSearch_QueryHelpers::getParams();

        if(is_null($delimiter)) {
            $delimiter = get_option('tag_delimiter') . ' ';
        }

        $tagString = '';

        if(!empty($tags)) {
            $tagStrings = array();

            if (is_array($tags)) {
                foreach($tags as $key => $tag) {
                    $tagStrings[$key] = SolrSearch_ViewHelpers::tagToString(
                        $uri, $current, $tag
                    );
                }

            } else {
                $parts = explode(',', $tags);
                foreach ($parts as $tag) {
                    $tagStrings[$tag] = SolrSearch_ViewHelpers::tagToString(
                        $uri, $current, trim($tag)
                    );
                }
            }

            $tagString = join(html_escape($delimiter), $tagStrings);
        }

        return $tagString;
    }

    /**
     * This takes atag and returns a string containing the tab label wrapped in 
     * an A.
     *
     * @param string $uri    The base URI for the links
     * @param array  $params The current set of search parameters.
     * @param string $tag    The tag to change to a wrapped string.
     *
     * @return string $a The A tag.
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    private static function tagToString($uri, $params, $tag)
    {
        $label = html_escape($tag);

        if (isset($params['facet'])) {
            $facetq = $params['facet'] . '+AND+tag:"' . $label .'"';
        } else {
            $facetq = 'tag:"' . $label .'"';
        }

        $searchpath = $uri . '?sorlq=' . $params['q'] . '&solrfacet=' . htmlspecialchars($facetq, ENT_QUOTES);
        $a = '<a href="' . $searchpath .'" reg="tag">' . $label . '</a>';

        return $a;
    }

    /**
     * This creates and returns the configuration form.
     *
     * @return Zend_Form
     * @author Eric Rochester <erochest@virginia.edu>
     */
    public static function makeConfigForm() {
        $form = new Zend_Form();
        SolrSearch_ViewHelpers::makeConfigFields($form);
        return $form;
    }

    /**
     * This creates the fields for the configuration form. If a form is passed 
     * in, the fields are added to it.
     *
     * @param Zend_Form|null $form The form to add the fields to.
     *
     * @return array $fields An associate array mapping option names to fields.
     * @author Eric Rochester <erochest@virginia.edu>
     */
    public static function makeConfigFields($form=null) {
        $fields = array();

        $fields[] = SolrSearch_ViewHelpers::makeOptionField(
            $form, 'solr_search_server', 'Server Host:', true
        );
        $fields[] = SolrSearch_ViewHelpers::makeOptionField(
            $form, 'solr_search_port', 'Server Port:', true
        )
            ->addValidator(new Zend_Validate_Digits());
        $fields[] = SolrSearch_ViewHelpers::makeOptionField(
            $form, 'solr_search_core', 'Solr Core Name:', true
        )
            ->addValidator('regex', true, array('/\/.*\//i'));

        $fields[] = SolrSearch_ViewHelpers::makeOptionField(
            $form, 'solr_search_rows', "Results Per Page:",
            false, "Defaults to Omeka's paging settings."
        )
            ->addValidator(new Zend_Validate_Digits())
            ->addErrorMessage('Results count must be numeric');

        $fields[] = SolrSearch_ViewHelpers::makeOptionField(
            $form, 'solr_search_facet_sort', 'Facet Field Constraint Order:', false,
            null, 'Zend_Form_Element_Select'
        )
            ->addMultiOption('index', 'Alphabetical')
            ->addMultiOption('count', 'Occurrences');

        $fields[] = SolrSearch_ViewHelpers::makeOptionField(
            $form, 'solr_search_facet_limit', 'Maximum Facet Count:', true
        )
            ->addValidator(new Zend_Validate_Digits());

        return $fields;
    }

    public static function makeOptionField(
        $form, $name, $label, $required, $descr=null,
        $cls='Zend_Form_Element_Text'
    ) {
        $field = new $cls($name, array(
            'label'    => $label,
            'value'    => get_option($name),
            'required' => $required
        ));
        if ($descr != null) {
            $field->setDescription($descr);
        }

        if ($form != null) {
            $form->addElement($field);
        }

        return $field;
    }

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
