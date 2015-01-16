<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>

<ul id="section-nav" class="navigation">
<?php
    SolrSearch_Utils::nav_li($tab, 'server',      url('solr-search/server'),      __('Server'));
    SolrSearch_Utils::nav_li($tab, 'collections', url('solr-search/collections'), __('Collections'));
    SolrSearch_Utils::nav_li($tab, 'fields',      url('solr-search/fields'),      __('Fields'));
    SolrSearch_Utils::nav_li($tab, 'results',     url('solr-search/results'),     __('Results'));
    SolrSearch_Utils::nav_li($tab, 'reindex',     url('solr-search/reindex'),     __('Index'));
?>
</ul>
