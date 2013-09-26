<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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
        return url('/solr-search/results/');
    }

    /**
     * This creates the default search form.
     *
     * @param string $buttonText     The text to use on the button.
     * @param array  $formProperties Extra HTML attributes to add to the
     *                               form element.
     *
     * @return string
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function createSearchForm(
        $buttonText=null, $formProperties=array( 'id' => 'simple-search' )
    ) {
        $buttonText  = (is_null($buttonText) ? __('Search') : $buttonText);
        $searchQuery = array_key_exists('solrq', $_GET) ? $_GET['solrq'] : '';

        $uri = SolrSearch_ViewHelpers::getBaseUrl();
        $formProperties['action'] = $uri;
        $formProperties['method'] = 'get';
        $html  = '<form ' . tag_attributes($formProperties) . '>' . "\n";
        $html .= '<fieldset>' . "\n\n";
        $html .= get_view()->formText('solrq', $searchQuery, array('name'=>'solrq', 'value' => $searchQuery, 'class'=>'textinput'));
        //$html .= get_view()->formHidden('solrfacet', '');
        $html .= get_view()->formSubmit('submit_search', $buttonText);
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
     * @param SolrDoc $doc Document to generate link for
     *
     * @return string Link to the model
     */
    public static function createResultLink($doc)
    {
        $url   = WEB_ROOT . $doc->url;
        $title = (is_null($doc->title) || $doc->title === '') ? '[Untitled]' : $doc->title;
        return "<a href='{$url}'>{$title}</a> <span class='solr-result-model'>{$doc->model}</span>";
    }

    /**
     * Return the title of doc
     *
     * TODO: limit DB lookups
     *
     * @param SolrDoc $doc Document to get title for
     *
     * @return string Title of the item
     */
    public static function getDocTitle($doc)
    {
        $title = null;

        if (isset($doc->title)) {
            $title = $doc->title;
        } else {
            $db    = get_db();
            $item  = $db->getTable('Item')->find($doc->modelid);
            $title = strip_formatting(item('Dublin Core', 'Title', $options, $item));
        }

        if (is_null($title) || $title === '') {
            $title = '[' . __('Untitled') . ']';
        }

        return $title;
    }

    /**
     * Return the path for an image
     *
     * @param string $type   Omeka File type (size)
     * @param int    $fileId Id of the file to look up
     *
     * @return string Link to file
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
     * @param int  $id           SolrDocument ID
     * @param bool $highlighting If the plugin should display highlights
     *
     * @return void
     */
    public static function displaySnippets($id, $highlighting)
    {
        if ($highlighting == null) {
            return;
        }

        $display = array();
        foreach ($highlighting as $k=>$v) {
            if ($k == $id) {
                foreach ($v as $k=>$snippets) {
                    $snippet = implode(' <strong>...</strong> ', $snippets);
                    $display[$snippet] = 1;
                }
            }
        }
        $display = array_keys($display);
        natcasesort($display);

        if (count($display) == 1) {
            echo "<p>{$display[0]}</p>";
        } else {
            echo "<ul class='hit-snippets'>";
            foreach ($display as $d) {
                echo "<li>$d</li>";
            }
            echo "</ul>";
        }
    }

    /**
     * Generate an image tag for use in search results.
     *
     * @param int    $image_id Image to look up
     * @param string $alt      Alt text for image
     *
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
     * @param array  $tags      An array of tags to display from a Solr result
     * @param string $delimiter ', ' (comma and whitespace) by default
     *
     * @return string tagString Facet link for given tag
     * @author Wayne Graham <wsg4w@virginia.edu>
     */
    public static function tagsToStrings($tags=array(), $delimiter=null)
    {
        $uri = SolrSearch_ViewHelpers::getBaseUrl();
        $current = SolrSearch_QueryHelpers::getParams();

        if (is_null($delimiter)) {
            $delimiter = get_option('tag_delimiter') . ' ';
        }

        $tagString = '';

        if (!empty($tags)) {
            $tagStrings = array();

            if (is_array($tags)) {
                foreach ($tags as $key => $tag) {
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
     */
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
    public static function makeConfigForm()
    {
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
    public static function makeConfigFields($form=null)
    {
        $fields = array();

        $fields[] = SolrSearch_ViewHelpers::makeOptionField(
            $form, 'solr_search_server', __('Server Host:'), true
        );
        $fields[] = SolrSearch_ViewHelpers::makeOptionField(
            $form, 'solr_search_port', __('Server Port:'), true
        )
        ->addValidator(new Zend_Validate_Digits());
        $fields[] = SolrSearch_ViewHelpers::makeOptionField(
            $form, 'solr_search_core', __('Solr Core Name:'), true
        )
        ->addValidator('regex', true, array('/\/.*\//i'));

        //$fields[] = SolrSearch_ViewHelpers::makeOptionField(
        //$form, 'solr_search_rows', __("Results Per Page:"),
        //false, __("Defaults to Omeka's paging settings.")
        //)
        //->addValidator(new Zend_Validate_Digits())
        //->addErrorMessage(__('Results count must be numeric'));

        $fields[] = SolrSearch_ViewHelpers::makeOptionField(
            $form, 'solr_search_facet_sort', __('Facet Field Constraint Order:'), false,
            null, 'Zend_Form_Element_Select'
        )
        ->addMultiOption('index', __('Alphabetical'))
        ->addMultiOption('count', __('Occurrences'));

        $fields[] = SolrSearch_ViewHelpers::makeOptionField(
            $form, 'solr_search_facet_limit', __('Maximum Facet Count:'), true
        )
        ->addValidator(new Zend_Validate_Digits());

        return $fields;
    }

    /**
     * Create an option field
     *
     * @param Zend_Form              $form     Zend form to process
     * @param string                 $name     Name of the option field
     * @param string                 $label    Option field label
     * @param boolean                $required If the field is required
     * @param string                 $descr    Description for the field
     * @param Zend_Form_Element_Text $cls      Form element
     *
     * @return Zend_Form_Element_Text Form element
     */
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

    /**
     * This takes a subform for the facet config form and renders it.
     *
     * This should really use decorators to display them the way we want.
     *
     * @param string    $group   The name of the group for the class.
     * @param Zend_Form $subform The subform to populate.
     *
     * @return string
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function createFacetSubForm($group, $subform)
    {
        $output  = '';
        $facetId = $subform->getElement('facetid');
        $label   = $subform->getElement('label');
        $options = $subform->getElement('options');
        $id      = preg_replace('/\W+/', '_', $label->getFullyQualifiedName());

        $output .= get_view()->partial(
            'config/_subform.php',
            array(
                'id'      => $id,
                'facetId' => $facetId,
                'label'   => $label
            )
        );

        foreach ($options->getMultiOptions() as $name => $optlabel) {
            $shortlab = explode(' ', $optlabel);
            $column   = strtolower($shortlab[1][0]);
            $output  .= '<td>';
            $output  .= "<input type='checkbox' name='{$options->getFullyQualifiedName()}' value='$name' ";
            if (in_array($name, $options->getValue())) {
                $output .= ' checked="checked"';
            }
            $output .= " class='g{$group}{$column}'/>";
            $output .= '</td>';
        }

        $output .= '</tr>';

        return $output;
    }

    /**
     * This creates the checkbox to select all children checkboxes.
     *
     * @param string $label The label for the checkbox.
     * @param string $group The group label.
     *
     * @return string
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function createSelectAll($label, $group, $column)
    {
        $output   = "$label <input form='' type='checkbox'";
        $output  .= " class='group-sel-all'";
        $output  .= " data-target='.g{$group}{$column}' />";

        return $output;
    }

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
