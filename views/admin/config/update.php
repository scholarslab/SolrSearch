<?php
    head(array('title' => 'Solr Search Facets', 'bodyclass' => 'primary', 'content_class' => 'horizontal-nav'));
?>
<h1>Manage Solr Search Facets</h1>

<div id="primary">
	<?php echo flash(); ?>
 	<?php
            if (!empty($err)) {
                echo '<p class="error">' . html_escape($err) . '</p>';
            }
        ?>
       
</div>

<?php 
    foot(); 
?>
