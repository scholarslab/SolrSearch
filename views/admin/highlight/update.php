<?php
    head(array('title' => 'Solr Search Hit Highlighting Options', 'bodyclass' => 'primary', 'content_class' => 'horizontal-nav'));
?>
<h1>Hit Highlighting</h1>

<div id="primary">
	<?php echo flash(); ?>
 	<?php
            if (!empty($err)) {
                echo '<p class="error">' . html_escape($err) . '</p>';
            }
        ?>
       <a href="index">Return to form.</a>
</div>

<?php 
    foot(); 
?>
