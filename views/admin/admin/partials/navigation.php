<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

function nav_li($tab, $key, $url, $label) {
    echo "<li";
    if ($tab == $key) {
        echo " class='current'";
    }
    echo "><a href='$url'>$label</a></li>\n";
}

?>

<ul id="section-nav" class="navigation">
<?php
    nav_li($tab, 'server',      url('solr-search/server'),      __('Server'));
    nav_li($tab, 'collections', url('solr-search/collections'), __('Collections'));
    nav_li($tab, 'fields',      url('solr-search/fields'),      __('Fields'));
    nav_li($tab, 'results',     url('solr-search/results'),     __('Results'));
    nav_li($tab, 'reindex',     url('solr-search/reindex'),     __('Index'));
?>
</ul>
