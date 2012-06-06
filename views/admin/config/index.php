<?php
  queue_js('accordion');
?>

<script type='text/javascript'>
function tip(el, form_name, revert_to) {
    jQuery(function() {
        jQuery(el).textinplace({
            form_name: form_name,
            revert_to: revert_to
        });
    });
}
</script>

<?php head(array(
  'title' => 'Solr Search Configuration',
  'bodyclass' => 'primary',
  'content_class' => 'horizontal-nav')
); ?>

<h1>Configure SolrSearch Indexing</h1>

<ul id="section-nav" class="navigation">
    <li class="current">
        <a href="<?php echo html_escape(uri('solr-search/config/')); ?>">Field Configuration</a>
    </li>
    <li class="">
        <a href="<?php echo html_escape(uri('solr-search/highlight/')); ?>">Hit Highlighting Options</a>
    </li>
    <li class="">
        <a href="<?php echo html_escape(uri('solr-search/reindex/')); ?>">Index Items</a>
    </li>
</ul>

<div id="primary">

    <h2>Configure Search Fields</h2>
    <?php echo flash(); ?>
    <?php if (!empty($err)) { echo '<p class="error">' . html_escape($err) . '</p>'; } ?>

    <form id="facets-form" method="post">

        <?php foreach ($form->getSubForms() as $group): ?>

          <h3 class="fieldset"><a href="#"><?php echo $group->getLegend(); ?></a></h3>
          <div>
          <table class="facet-fields">
      <thead>
        <tr>
        <?php browse_headings(array(
          'Field'         => null,
          'Is Searchable' => null,
          'Is Facet'      => null
        )); ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($group->getSubForms() as $eform): ?>
          <?php echo SolrSearch_ViewHelpers::createFacetSubForm($eform); ?>
        <?php endforeach; ?>
      </tbody>
          </table>
          </div>

        <?php endforeach; ?>

    </table>
    <?php echo $form->getElement('submit'); ?>
    </form></div>

<?php foot(); ?>
