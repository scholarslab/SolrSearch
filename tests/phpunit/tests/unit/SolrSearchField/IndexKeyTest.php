<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */


class SolrSearchFieldTest_IndexKey extends SolrSearch_Case_Default
{


    /**
     * `indexKey` should return the `_t`-suffixed slug.
     */
    public function testIndexKey()
    {
        $field = $this->_field('field');
        $this->assertEquals('field_t', $field->indexKey());
    }


}
