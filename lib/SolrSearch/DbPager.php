<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
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
    function __construct($db, $table, $select, $rowCount=100, $params=array())
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
