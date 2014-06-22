<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>

<?php echo head(array(
  'title' => __('Solr Search | Index Items')
)); ?>

<?php echo $this->partial('admin/partials/navigation.php', array(
  'tab' => 'reindex'
)); ?>

<div id="primary">
  <h2><?php echo __('Index Items') ?></h2>
  <?php echo flash(); ?>
  <p><?php echo __('Click the button to (re)index the entire site.') ?></p>
  <?php echo $form ?>
</div>

<?php echo foot(); ?>
