<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFacet extends Omeka_Record_AbstractRecord
{


    /**
     * The id of the parent element [integer].
     */
    public $element_id;

    /**
     * The name of the element [string].
     */
    public $name;

    /**
     * The label of the element.
     *
     * @var string
     **/
    public $label;

    /**
     * The id of the parent element set [integer].
     */
    public $element_set_id;

    /**
     * Facet status [boolean/tinyint].
     */
    public $is_facet;

    /**
     * Displayed status [boolean/tinyint].
     */
    public $is_displayed;


    /**
     * Get the parent element.
     *
     * @return Element|null The element.
     */
    public function getElement()
    {
        return $this->getTable('Element')->find($this->element_id);
    }


    /**
     * Get the parent element set.
     *
     * @return ElementSet|null The element set.
     */
    public function getElementSet()
    {
        $element = $this->getElement();
        if ($element) return $element->getElementSet();
    }

    /**
     * This returns the original value for this facet, if it can be determined.
     *
     * @return string|null
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function getOriginalValue()
    {
        switch ($this->name) {

            case 'tag':         return __('Tag');
            case 'collection':  return __('Collection');
            case 'itemtype':    return __('Item Type');
            case 'resulttype':  return __('Result Type');

            default: return $this->getElement()->name;

        }
    }

}
