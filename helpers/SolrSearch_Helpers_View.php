<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_Helpers_View
{


    /**
     * This returns the base URL for the results page.
     *
     * @return string
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public static function getBaseUrl()
    {
        return url('solr-search/results');
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
        $fullsize_path = SolrSearch_Helpers_View::getImagePath(
            'fullsize', $image_id
        );
        $thumb_path = SolrSearch_Helpers_View::getImagePath(
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
        $uri = SolrSearch_Helpers_View::getBaseUrl();
        $current = SolrSearch_Helpers_Query::getParams();

        if (is_null($delimiter)) {
            $delimiter = get_option('tag_delimiter') . ' ';
        }

        $tagString = '';

        if (!empty($tags)) {
            $tagStrings = array();

            if (is_array($tags)) {
                foreach ($tags as $key => $tag) {
                    $tagStrings[$key] = SolrSearch_Helpers_View::tagToString(
                        $uri, $current, $tag
                    );
                }

            } else {
                $parts = explode(',', $tags);
                foreach ($parts as $tag) {
                    $tagStrings[$tag] = SolrSearch_Helpers_View::tagToString(
                        $uri, $current, trim($tag)
                    );
                }
            }

            $tagString = join(html_escape($delimiter), $tagStrings);
        }

        return $tagString;
    }


    /**
     * This takes a tag and returns a string containing the tab label wrapped in 
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

        $searchpath = $uri . '?q=' . $params['q'] . '&facet=' . htmlspecialchars($facetq, ENT_QUOTES);
        $a = '<a href="' . $searchpath .'" reg="tag">' . $label . '</a>';

        return $a;
    }


}
