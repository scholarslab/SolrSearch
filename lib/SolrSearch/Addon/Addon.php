<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


/**
 * This contains the data for the main information for an addon.
 **/
class SolrSearch_Addon_Addon
{


    /**
     * The name of the addon.
     *
     * @var string
     **/
    var $name;

    /**
     * The value for the result-type facet.
     *
     * @var string|null
     **/
    var $resultType;

    /**
     * The name of the table. This doesn't need to have the prefix.
     *
     * @var string
     **/
    var $table;

    /**
     * The ID column for this table. This defaults to 'id'.
     *
     * @var string
     **/
    var $idColumn;

    /**
     * If this is the child in a hierarchy, this is the parent. If this is set,
     * this object should appear in the parent's children property.
     *
     * @var SolrSearch_Addon_Addon|null
     **/
    var $parentAddon;

    /**
     * The database key to use to get from this object to the parent.
     *
     * @var string|null
     **/
    var $parentKey;

    /**
     * Does this type implement Taggable?
     *
     * @var bool
     **/
    var $tagged;

    /**
     * If set, this is a field that acts as a visible flag. If it is set to
     * TRUE, then the item should be indexed. Otherwise, it will be skipped.
     *
     * @var string|null
     **/
    var $flag;

    /**
     * An array list of fields in this.
     *
     * @var array of SolrSearch_Addon_Field
     **/
    var $fields;

    /**
     * An array list of children of this addon.
     *
     * @var array of SolrSearch_Addon_Addon
     **/
    var $children;


    function __construct(
        $name=null, $resultType=null, $table=null, $idColumn='id',
        $parentAddon=null, $parentKey=null, $tagged=false, $flag=null
    ) {
        $this->name        = $name;
        $this->resultType  = $resultType;
        $this->table       = $table;
        $this->idColumn    = $idColumn;
        $this->parentAddon = $parentAddon;
        $this->parentKey   = $parentKey;
        $this->tagged      = $tagged;
        $this->flag        = $flag;
        $this->fields      = array();
        $this->children    = array();
    }


    /**
     * This tests whether this addon has a flag anywhere up its ancenstors.
     *
     * @return bool
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function hasFlag()
    {
        $flag = false;

        if (is_null($this->parentAddon)) {
            $flag = !is_null($this->flag);
        } else {
            $flag = $this->parentAddon->hasFlag();
        }

        return $flag;
    }


    /**
     * This returns the field marked title, named title, or null for this
     * addon.
     *
     * @return SolrSearch_Addon_Field|null
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function getTitleField()
    {
        $result  = null;
        $named   = null;
        $flagged = null;

        foreach ($this->fields as $field) {
            if ($field->name === 'title') {
                $named = $field;
            }
            if ($field->is_title) {
                $flagged = $field;
            }
        }

        if (!is_null($flagged)) {
            $result = $flagged;
        } else if (!is_null($named)) {
            $result = $named;
        }

        return $result;
    }


}
