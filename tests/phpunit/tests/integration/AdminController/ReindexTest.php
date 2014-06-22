<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

class AdminControllerTest_Reindex extends SolrSearch_Case_Default
{


    /**
     * REINDEX should dispatch the reindex job.
     */
    public function testReindex()
    {

        $jobs = $this->_mockJobDispatcher();

        // Should dispatch `SolrSearch_Job_Reindex`.
        $jobs->expects($this->once())->method('sendLongRunning')->with(
            'SolrSearch_Job_Reindex'
        );

        // Trigger a reindex.
        $this->request->setMethod('POST');
        $this->dispatch('solr-search/reindex');

    }


}
