<?php
head(array('title' => 'Reindex Items in Solr', 'bodyclass' => 'primary', 'content_class' => 'horizontal-nav'));
?>

<h1>Configure Solr</h1>

<ul id="section-nav" class="navigation">
    <li class="">
        <a href="<?php echo html_escape(uri('solr-search/config/')); ?>">Field Configuration</a>
    </li>
    <li class="">
        <a href="<?php echo html_escape(uri('solr-search/highlight/')); ?>">Hit Highlighting Options</a>
    </li>
    <li class="current">
        <a href="<?php echo html_escape(uri('solr-search/reindex/')); ?>">Index Items</a>
    </li>
</ul>
<div id="primary">
  <h2>Reindex All Items</h2>

   <?php echo flash(); ?>
<?php
if (!empty($err)) {
  echo '<p class="error">' . html_escape($err) . '</p>';
}
?>
    <p>Click the button below to reindex all of your public items into the Solr index.</p>
  <?php echo $form ?>
</div>

<?php
foot();
