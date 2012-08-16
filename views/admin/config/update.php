<?php
    head(array('title' => __('Solr Search Facets'), 'bodyclass' => 'primary', 'content_class' => 'horizontal-nav'));
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
    foot(); 
?>
