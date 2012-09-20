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
  'title' => __('Solr Search Configuration'),
  'bodyclass' => 'primary',
  'content_class' => 'horizontal-nav')
); ?>

<h1><?php echo __('Configure SolrSearch Indexing') ?></h1>

<ul id="section-nav" class="navigation">
    <li class="current">
        <a href="<?php echo html_escape(uri('solr-search/config/')); ?>"><?php echo __('Field Configuration') ?></a>
    </li>
    <li class="">
        <a href="<?php echo html_escape(uri('solr-search/highlight/')); ?>"><?php echo __('Hit Highlighting Options') ?></a>
    </li>
    <li class="">
        <a href="<?php echo html_escape(uri('solr-search/reindex/')); ?>"><?php echo __('Index Items') ?></a>
    </li>
</ul>

<div id="primary">

<h2><?php echo __('Configure Search Fields') ?></h2>
    <?php echo flash(); ?>
    <?php if (!empty($err)) { echo '<p class="error">' . html_escape($err) . '</p>'; } ?>

    <form id="facets-form" method="post">

        <?php foreach ($form->getSubForms() as $group): ?>

          <h3 class="fieldset"><a href="#"><?php echo $group->getLegend(); ?></a></h3>
          <div>
          <table class="facet-fields">
      <thead>
        <tr>
<?php
$n             = $group->getName();
$is_searchable = SolrSearch_ViewHelpers::createSelectAll(__('Is Searchable'), $n, 's');
$is_facet      = SolrSearch_ViewHelpers::createSelectAll(__('Is Facet'), $n, 'f');
browse_headings(array(
    __('Field')    => null,
    $is_searchable => null,
    $is_facet      => null
));
?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($group->getSubForms() as $eform): ?>
          <?php echo SolrSearch_ViewHelpers::createFacetSubForm($n, $eform); ?>
        <?php endforeach; ?>
      </tbody>
          </table>
          </div>

        <?php endforeach; ?>

    </table>
    <?php echo $form->getElement('submit'); ?>
<script type='text/javascript'>
jQuery(function () {
    jQuery('.group-sel-all').change(function (event) {
        var obj = jQuery(this);
        var val = obj.attr('checked') === 'checked' ? 'checked' : null;
        jQuery(obj.attr('data-target')).attr('checked', val);
    });
});
</script>
    </form></div>

<?php foot(); ?>
