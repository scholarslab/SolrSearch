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
     * Get the parent element set.
     *
     * @return Omeka_Record The element set.
     */
    public function getElementSet()
    {
        $_elementSetTable = $this->getTable('ElementSet');
        return $_elementSetTable->find($this->element_set_id);
    }

    /**
     * This returns the original value for this facet, if it can be determined.
     *
     * @return string|null
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function getOriginalValue()
    {
        $original = null;

        switch ($this->name) {
            case 'tag'        : $original = __('Tag');         break;
            case 'collection' : $original = __('Collection');  break;
            case 'itemtype'   : $original = __('Item Type');   break;
            case 'resulttype' : $original = __('Result Type'); break;

            default:
                $etable   = $this->getTable('Element');
                $e        = $etable->find($this->element_id);
                $original = $e->name;
                // code...
                break;
        }

        return $original;
    }

}
