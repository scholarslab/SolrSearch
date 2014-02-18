<?php

/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>

<?php queue_js_file('payloads/fields'); ?>
<?php queue_css_file('fields'); ?>

<?php echo head(array(
  'title' => __('Solr Search | Field Configuration'),
)); ?>

<div id="solr_config">

  <?php echo $this->partial('admin/partials/navigation.php', array(
    'tab' => 'fields'
  )); ?>

  <div id="primary">

    <h2><?php echo __('Field Configuration') ?></h2>
    <?php echo flash(); ?>

    <form id="facets-form" method="post">
      <?php foreach ($form->getSubForms() as $group): ?>

        <h3 class="fieldset">
          <a href="#"><?php echo $group->getLegend(); ?></a>
        </h3>

        <div>
          <table class="facet-fields">

            <thead>
              <tr>
                <th><?php echo __('Field'); ?></th>
                <th><?php echo __('Is Searchable'); ?></th>
                <th><?php echo __('Is Facet'); ?></th>
              </tr>
            </thead>

            <tbody>
              <?php foreach ($group->getSubForms() as $facet): ?>
                <?php echo SolrSearch_Helpers_View::createFacetSubForm(
                  $group->getName(), $facet
                ); ?>
              <?php endforeach; ?>
            </tbody>

          </table>
        </div>

        <?php endforeach; ?>

      <?php echo $this->formSubmit('submit', __('Update Search Fields')); ?>

    </form>

  </div>
</div>

<?php echo foot(); ?>
