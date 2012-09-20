<?php
head(array('title' => __('Reindex Items in Solr'), 'bodyclass' => 'primary', 'content_class' => 'horizontal-nav'));
?>

<h1><?php echo __('Configure Solr') ?></h1>

<ul id="section-nav" class="navigation">
    <li class="">
        <a href="<?php echo html_escape(uri('solr-search/config/')); ?>"><?php echo __('Field Configuration') ?></a>
    </li>
    <li class="">
        <a href="<?php echo html_escape(uri('solr-search/highlight/')); ?>"><?php echo __('Hit Highlighting Options') ?></a>
    </li>
    <li class="current">
        <a href="<?php echo html_escape(uri('solr-search/reindex/')); ?>"><?php echo __('Index Items') ?></a>
    </li>
</ul>
<div id="primary">
    <h2><?php echo __('Reindex All Items') ?></h2>

   <?php echo flash(); ?>
<?php
if (!empty($err)) {
  echo '<p class="error">' . html_escape($err) . '</p>';
}
?>
    <p><?php echo __('Click the button below to reindex all of your public items into the Solr index.') ?></p>
  <?php echo $form ?>
</div>

<?php
foot();
