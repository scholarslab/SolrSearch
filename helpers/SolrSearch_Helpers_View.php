<?php

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
        return url('solr-search');
    }


    /**
     * Lookup the element name for a solr element.
     *
     * @param string $field Field name to look up.
     *
     * @return string Human readable solr element name.
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
     * Return the path for an image.
     *
     * @param string $type Omeka File type (size).
     * @param int $fileId Id of the file to look up.
     *
     * @return string Link to file.
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
     * @param int $image_id Image to look up.
     * @param string $alt Alt text for image.
     *
     * @return string $html Link to image.
     * @author Wayne Graham <wsg4w@virginia.edu>
     **/
    public static function createResultImgHtml($image_id, $alt)
    {
        $html = '';
        $thumbnail  = self::getImagePath('square_thumbnail', $image_id);
        $fullsize   = self::getImagePath('fullsize', $image_id);

        $html .= '<a class="solr_search_image" href="' . $fullsize . '">';
        $html .= '<img alt="' . $alt . '" src="' . $thumbnail . '" />';
        $html .= '</a>';

        return $html;
    }


    /**
     * Get the URL for the record that corresponds to a Solr document.
     *
     * @param Apache_Solr_Document $doc A Solr document.
     *
     * @return string The record URL.
     */
    public static function getDocumentUrl($doc)
    {
        $record = get_db()->getTable($doc->model)->find($doc->modelid);
        return SolrSearch_Helpers_Index::getUri($record);
    }


}
