<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

function nav_li($key, $url, $label) {
    global $tab;
    echo "<li";
    if ($tab == $key) {
        echo " class='current'";
    }
    echo "><a href='$url'>$label</a></li>\n";
}

?>

<ul id="section-nav" class="navigation">
<?php
    nav_li('server',  url('solr-search/server'),  __('Server Configuration'));
    nav_li('fields',  url('solr-search/fields'),  __('Field Configuration'));
    nav_li('results', url('solr-search/results'), __('Results Configuration'));
    nav_li('reindex', url('solr-search/reindex'), __('Index Items'));
?>
</ul>
