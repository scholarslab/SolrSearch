<?php 
  queue_js('accordion');
?>

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
          <?php $facetIds = ''; ?>
          <?php foreach ($group->getSubForms() as $eform): ?>
            <tr>
            <?php foreach ($eform->getElements() as $element): ?>
            <?php
                /*********************************************************************/
                /** These should use decorators to display the way we want them to. **/
                /*********************************************************************/
              ?>
              <?php if ($element->getName() === 'facetid'): ?>
                <?php $facetIds .= "<input name='{$element->getFullyQualifiedName()}' type='hidden' value='{$element->getValue()}' />"; ?>
              <?php elseif ($element->getName() === 'label'): ?>
                  <td class="element">
                    <div id='<?php echo $element->getFullyQualifiedName(); ?>' class='facetlabel'><?php echo $element->getValue(); ?></div>
                  </td>
              <?php elseif ($element->getName() === 'options'): ?>
                  <?php foreach ($element->getMultiOptions() as $name => $label): ?>
                    <td><input type="checkbox" name="<?php echo $element->getFullyQualifiedName(); ?>" value="<?php echo $name; ?>" <?php if (in_array($name, $form->getValue($element->getName()))) { echo 'checked="checked"'; } ?>/></td>
                  <?php endforeach; ?>
                </tr>
              <?php endif; ?>
            <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
          <?php echo $facetIds; ?>
      </tbody>
          </table>
          </div>

        <?php endforeach; ?>

    </table>
    <?php echo $form->getElement('submit'); ?>
    </form></div>
<script type='text/javascript'>
jQuery(function() {
    var fl = jQuery('.facetlabel').textinplace();
    });
</script>

<?php foot(); ?>
