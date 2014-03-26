<?php

/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>

<ul id="section-nav" class="navigation">
  <li class="<?php if ($tab == 'server') echo 'current'; ?>">
    <a href="<?php echo url('solr-search/server'); ?>">
      <?php echo __('Server Configuration') ?>
    </a>
  </li>
  <li class="<?php if ($tab == 'fields') echo 'current'; ?>">
    <a href="<?php echo url('solr-search/fields'); ?>">
      <?php echo __('Field Configuration') ?>
    </a>
  </li>
  <li class="<?php if ($tab == 'results') echo 'current'; ?>">
    <a href="<?php echo url('solr-search/results'); ?>">
      <?php echo __('Results Configuration') ?>
    </a>
  </li>
  <li class="<?php if ($tab == 'reindex') echo 'current'; ?>">
    <a href="<?php echo url('solr-search/reindex'); ?>">
      <?php echo __('Index Items') ?>
    </a>
  </li>
</ul>
