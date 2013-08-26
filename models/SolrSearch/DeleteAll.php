<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

class SolrSearch_DeleteAll extends Omeka_Job_Process_AbstractProcess
{
    public function run($args)
    {
        try {
            SolrSearch_IndexAll::deleteAll(array());
        } catch ( Exception $e ) {
            $this->_log($e->getMessage());
            echo $e->getMessage();
        }
    }
}
