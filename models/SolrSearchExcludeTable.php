<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2014 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchExcludeTable extends Omeka_Db_Table
{
    /**
     * This tests whether the collection with the given ID is excluded.
     *
     * @param integer $collection_id The ID of the collection to test.
     * @return bool
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function isExcludedID($collection_id)
    {
        $count = $this->count(array(
            'collection_id' => $collection_id
        ));
        return ($count > 0);
    }

    /**
     * This tests whether the collection is excluded.
     *
     * @param Collection $collection The collection to test.
     * @return bool
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function isExcluded($collection)
    {
        return $this->isExcludedID($collection->id);
    }
}
