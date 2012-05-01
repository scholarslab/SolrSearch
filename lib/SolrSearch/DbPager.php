<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 *
 * PHP version 5
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package     omeka
 * @subpackage  fedoraconnector
 * @author      Scholars' Lab <>
 * @author      Ethan Gruber <ewg4x@virginia.edu>
 * @author      Adam Soroka <ajs6f@virginia.edu>
 * @author      Wayne Graham <wayne.graham@virginia.edu>
 * @author      Eric Rochester <err8n@virginia.edu>
 * @copyright   2010 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 * @version     $Id$
 * @link        http://omeka.org/add-ons/plugins/SolrSearch/
 */

/**
 * This class takes a Zend_Db_Select object and pages through the results, 
 * returning them in chunks.
 *
 * IT IS STRONGLY SUGGESTED THAT THE SELECT HAVE AN ORDER CLAUSE.
 **/
class SolrSearch_DbPager
{

    /**
     * This is the database object.
     *
     * @var Zend_Db
     **/
    var $db;

    /**
     * This is the Omeka_Db_Table object to pull objects with.
     *
     * @var Omeka_DB_Table
     **/
    var $table;

    /**
     * This is the Zend_Db_Select instance to query.
     *
     * @var Zend_Db_Select
     **/
    var $select;

    /**
     * This is the number of page that was retrieved. This starts out at 0.
     *
     * @var int
     **/
    var $pageNumber;

    /**
     * This is the number of items to return in each chunk.
     *
     * @var int
     **/
    var $rowCount;

    /**
     * An array of parameters to pass to the query every time it's executed.
     *
     * @var array
     **/
    var $params;


    /**
     * This constructs a SolrSearch_DbPager.
     **/
    function __construct($db, $table, $select, $rowCount=1000, $params=array())
    {
        $this->db         = $db;
        $this->table      = $table;
        $this->select     = $select;
        $this->pageNumber = 0;
        $this->rowCount   = $rowCount;
        $this->params     = $params;
    }

    /**
     * This returns the next chunk of database result objects.
     *
     * @return array|null Set of Omeka_Record instances, or null if none can be
     * found.
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function next()
    {
        $this->pageNumber++;
        $this->select->limitPage($this->pageNumber, $this->rowCount);

        $rows = $this->table->fetchObjects($this->select, $this->params);
        return $rows;
    }
}


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

