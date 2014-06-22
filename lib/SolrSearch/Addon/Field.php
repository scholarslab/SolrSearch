<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


/**
 * This contains the data for the main information for an addon field.
 **/
class SolrSearch_Addon_Field
{


    /**
     * The Solr name of the field.
     *
     * @var string
     **/
    var $name;

    /**
     * The display label for the field.
     *
     * @var string
     **/
    var $label;

    /**
     * Is this field a facet?
     *
     * @var bool
     **/
    var $is_facet;

    /**
     * Should this field be used for the Solr documet title?
     *
     * @var bool
     **/
    var $is_title;

    /**
     * This is an array containing the table and key to a remote location for
     * the data in this field.
     *
     * @var array|null
     **/
    var $remote;


    function __construct(
        $name=null, $label=null, $is_facet=null, $is_title=null, $remote=null
    ) {
        $this->name     = $name;
        $this->label    = $label;
        $this->is_facet = $is_facet;
        $this->is_title = $is_title;
        $this->remote   = $remote;
    }


}
