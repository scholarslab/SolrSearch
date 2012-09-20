<?php
    head(array('title' => __('Solr Search Hit Highlighting Options'), 'bodyclass' => 'primary', 'content_class' => 'horizontal-nav'));
?>
<h1><?php echo __('Configure Solr') ?></h1>

<ul id="section-nav" class="navigation">
    <li class="">
        <a href="<?php echo html_escape(uri('solr-search/config/')); ?>"><?php echo __('Field Configuration') ?></a>
    </li>
    <li class="current">
        <a href="<?php echo html_escape(uri('solr-search/highlight/')); ?>"><?php echo __('Hit Highlighting Options') ?></a>
    </li>
    <li class="">
        <a href="<?php echo html_escape(uri('solr-search/reindex/')); ?>"><?php echo __('Index Items') ?></a>
    </li>
</ul>
<div id="primary">
    <h2><?php echo __('Hit Highlighting') ?></h2>
   <?php echo flash(); 
            if (!empty($err)) {
                echo '<p class="error">' . html_escape($err) . '</p>';
            }
        ?>
    <p><?php echo __('Select hit highlighting options from available fields below.') ?></p>
	<?php echo $form ?>
</div>

<?php 
    foot(); 
?>
