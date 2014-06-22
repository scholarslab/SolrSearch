<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>

<?php echo head(array(
  'title' => __('Solr Search | Results Configuration')
)); ?>

<?php echo $this->partial('admin/partials/navigation.php', array(
  'tab' => 'results'
)); ?>

<div id="primary">
  <h2><?php echo __('Results Configuration') ?></h2>
	<?php echo flash(); ?>
	<?php echo $form; ?>
</div>

<?php echo foot(); ?>
