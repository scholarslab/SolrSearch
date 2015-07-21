<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>

<?php queue_js_file('accordion'); ?>
<?php queue_css_file('fields'); ?>

<?php echo head(array(
  'title' => __('Solr Search | Field Configuration'),
)); ?>

<div id="solr-fields">

  <?php echo $this->partial('admin/partials/navigation.php', array(
    'tab' => 'fields'
  )); ?>

  <div id="primary">

    <h2><?php echo __('Field Configuration') ?></h2>
    <?php echo flash(); ?>

    <form id="facets-form" method="post">
      <?php foreach ($groups as $name => $group): ?>

        <h3 class="fieldset">
          <a href="#"><?php echo $name; ?></a>
        </h3>

        <div>
          <table class="facet-fields">

            <thead>
              <tr>
                <th><?php echo __('Field'); ?></th>
                <th><?php echo __('Facet Label'); ?></th>
                <th><?php echo __('Is Indexed?'); ?></th>
                <th><?php echo __('Is Facet?'); ?></th>
              </tr>
            </thead>

            <tbody>
              <?php foreach ($group as $field): ?>
                <?php echo $this->partial('admin/partials/field.php', array(
                  'field' => $field
                )); ?>
              <?php endforeach; ?>
            </tbody>

          </table>
        </div>

        <?php endforeach; ?>

      <a class="button" href="<?php echo url('solr-search/updatefacet'); ?>">
        Load New Elements
      </a>
      <?php echo $this->formSubmit('submit', __('Update Search Fields')); ?>

    </form>

  </div>
</div>

<?php echo foot(); ?>
