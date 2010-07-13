<?php
    head(array('title' => 'Solr Search Configuration', 'bodyclass' => 'primary', 'content_class' => 'horizontal-nav'));
?>
<h1>Configure Solr</h1>

<div id="primary">
	<h2>Select Facet Fields</h2>
   <?php echo flash(); ?>
	<?php
            if (!empty($err)) {
                echo '<p class="error">' . html_escape($err) . '</p>';
            }
        ?>
    <p>Select facets from available fields below.</p>
	<?php echo $form ?>
</div>

<?php 
    foot(); 
?>
