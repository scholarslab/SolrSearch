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
 * This contains the data for the main information for an addon field.
 **/
class SolrSearch_Addon_Field
{
    //{{{Properties

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

    //}}}

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

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
