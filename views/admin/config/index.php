<?php queue_js('accordion'); ?>

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

    <h2>Select Facet Fields</h2>
    <?php echo flash(); ?>
    <?php if (!empty($err)) { echo '<p class="error">' . html_escape($err) . '</p>'; } ?>

    <form id="facets-form" method="post" action="update">
    <table>
      <thead>
        <tr>
        <?php browse_headings(array(
          'Field' => null,
          'Is Displayed' => null,
          'Is Facet' => null,
          'Is Visible' => null
        )); ?>
        </tr>
      </thead>
      <tbody>

        <?php foreach ($form->getDisplayGroups() as $group): ?>

          <tr class="fieldset"><td colspan="4"><?php echo $group->getLegend(); ?></td></tr>

          <?php foreach ($group->getElements() as $element): ?>
            <tr>
              <td><?php echo $element->getName(); ?></td>
            </tr>
          <?php endforeach; ?>

        <?php endforeach; ?>

      </tbody>
    </table>
    <?php echo $form->getElement('submit'); ?>
    </form></div>

<?php foot(); ?>
