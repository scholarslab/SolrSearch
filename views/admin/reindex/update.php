<?php

/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=80; */

/**
 * @package     omeka
 * @subpackage  fedora-connector
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>

<?php
    echo head(array('title' => __('Solr Search Facets'), 'bodyclass' => 'primary', 'content_class' => 'horizontal-nav'));
?>
<h1><?php echo __('Manage Solr Search Facets') ?></h1>

<div id="primary">
	<?php echo flash(); ?>
 	<?php
            if (!empty($err)) {
                echo '<p class="error">' . html_escape($err) . '</p>';
            }
        ?>
        <a href="index"><?php echo __('Return to form.') ?></a>
</div>

<?php 
    echo foot(); 
?>
