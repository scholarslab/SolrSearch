<?php
    head(array('title' => 'Solr Search Hit Highlighting Options', 'bodyclass' => 'primary', 'content_class' => 'horizontal-nav'));
?>
<h1>Hit Highlighting</h1>

<ul id="section-nav" class="navigation">
    <li class="">
        <a href="<?php echo html_escape(uri('solr-search/config/')); ?>">Field Configuration</a>
    </li>
    <li class="current">
        <a href="<?php echo html_escape(uri('solr-search/highlight/')); ?>">Hit Highlighting Options</a>
    </li>
</ul>
<div id="primary">
	<h2>Select Facet Fields</h2>
   <?php echo flash(); ?>
	<?php
            if (!empty($err)) {
                echo '<p class="error">' . html_escape($err) . '</p>';
            }
        ?>
    <p>Select hit highlighting options from available fields below.</p>
	<?php echo $form ?>
</div>

<?php 
    foot(); 
?>
