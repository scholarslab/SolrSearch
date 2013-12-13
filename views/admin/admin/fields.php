<?php

/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=80; */

/**
 * @package     omeka
 * @subpackage  fedora-connector
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>

<?php
  queue_js_file('accordion');
  echo head(array(
    'title' => __('Solr Search Configuration'),
    'bodyclass' => 'primary',
    'content_class' => 'horizontal-nav')
  );
?>

<script type='text/javascript'>
  function tip(el, form_name, revert_to) {
    jQuery(function($) {
      $(el).textinplace({
        form_name: form_name,
        revert_to: revert_to
      });
    });
  }
</script>

<div id="solr_config">

  <?php echo $this->partial('admin/partials/navigation.php', array(
    'tab' => 'fields'
  )); ?>

  <div id="primary">

    <h2><?php echo __('Configure Search Fields') ?></h2>
    <?php echo flash(); ?>

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
                ?>
                <th><?php echo __('Field');    ?></th>
                <th><?php echo $is_searchable; ?></th>
                <th><?php echo $is_facet;      ?></th>
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

      <?php echo $form->getElement('submit'); ?>

      <script type='text/javascript'>
        jQuery(function ($) {
          $('.group-sel-all').change(function(event) {
            var checkbox = $(this);
            $(checkbox.attr('data-target')).prop(
              'checked', checkbox.is(':checked')
            );
          });
        });
      </script>

    </form>

  </div>
</div>

<?php echo foot(); ?>
