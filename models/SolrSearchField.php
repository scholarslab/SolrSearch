<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchField extends Omeka_Record_AbstractRecord
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
     **/
    public $label;

    /**
     * Displayed status [boolean/tinyint].
     */
    public $is_indexed;

    /**
     * Facet status [boolean/tinyint].
     */
    public $is_facet;


    /**
     * Set the parent element reference.
     *
     * @param Element $element The parent element.
     */
    public function __construct($element=null)
    {

        parent::__construct();

        if (!is_null($element)) {

            // Element reference.
            $this->element_id = $element->id;

            // Element identifier.
            $this->name = "{$element->id}_s";

            // Pubilc-facing label.
            $this->label = $element->name;

        }

    }


    /**
     * Is the field associated with a metadata element?
     *
     * @return boolean True if an element is defined.
     */
    public function hasElement()
    {
        return !is_null($this->element_id);
    }


    /**
     * Get the parent element.
     *
     * @return Element|null The element.
     */
    public function getElement()
    {
        if (!$this->hasElement()) return null;
        else return $this->getTable('Element')->find($this->element_id);
    }


    /**
     * Get the parent element set.
     *
     * @return ElementSet|null The element set.
     */
    public function getElementSet()
    {
        if (!$this->hasElement()) return null;
        else return $this->getElement()->getElementSet();
    }


    /**
     * Get the name of the parent element set.
     *
     * @return string The element set name.
     */
    public function getElementSetName()
    {
        if (!$this->hasElement()) return __('Omeka Categories');
        else return $this->getElementSet()->name;
    }


    /**
     * Return the original label for the field.
     *
     * @return string|null
     **/
    public function getOriginalLabel()
    {
        switch ($this->name) {

            case 'tag':         return __('Tag');
            case 'collection':  return __('Collection');
            case 'itemtype':    return __('Item Type');
            case 'resulttype':  return __('Result Type');

            default: return $this->getElement()->name;

        }
    }


    /**
     * If the label is empty, revert to the original label.
     *
     * @return string The facet label.
     */
    public function beforeSave()
    {
        $label = trim($this->label);
        if (empty($label)) $this->label = $this->getOriginalLabel();
    }


}
