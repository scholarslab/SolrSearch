<?php

/**
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearch_Job_Reindex extends Omeka_Job_AbstractJob
{


    /**
     * Reindex all records.
     */
    public function perform()
    {
        if (isset($this->_options['clear']) && $this->_options['clear']) {
            SolrSearch_Helpers_Index::deleteAll();
        }

        SolrSearch_Helpers_Index::indexAll();
    }


}
