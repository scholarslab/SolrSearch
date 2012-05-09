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
 * This handles indexes data from the addons.
 **/
class SolrSearch_Addon_Indexer
{

    /**
     * This creates a Solr-style name for an addon and field.
     *
     * @param SolrSearch_Addon_Addon $addon This is the addon.
     * @param string                 $field The field to get.
     *
     * @return string $name The Solr name.
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function makeSolrName($addon, $field)
    {
        return "{$addon->name}_{$field}_s";
    }

    /**
     * This gets all the records in the database matching all the addons passed 
     * in and returns a list of Solr documents for indexing.
     *
     * @param associative array of SolrSearch_Addon_Addon $addons The addon 
     * configuration information about the records to index.
     *
     * @return array of Apache_Solr_Document $docs The documents to index.
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function indexAll($addons)
    {
    }

    /**
     * This gets all the records associated with a single addon for indexing.
     *
     * @param SolrSearch_Addon_Addon The addon to pull records for.
     *
     * @return array of Apache_Solr_Documents $docs The documents to index.
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function indexAllAddon($addon)
    {
    }

    /**
     * This returns an Apache_Solr_Document to index, if the addons say it 
     * should be.
     *
     * @param Omeka_Record $record The record to index.
     * @param associative array of SolrSearch_Addon_Addon $addons The 
     * configuration controlling how records are indexed.
     *
     * @return Apache_Solr_Document|null
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function indexRecord($record, $addon)
    {
    }

    /**
     * This builds a query for returning all the records to index from the 
     * database.
     *
     * @param SolrSearch_Addon_Addon $addon The addon to generate SQL for.
     *
     * @return Omeka_Db_Select $select The select statement to execute for the 
     * query.
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function buildSelect($addon)
    {
    }

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
