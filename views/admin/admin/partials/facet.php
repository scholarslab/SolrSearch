<?php

/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>

<tr>
  <td class="element">

  <input
    type="hidden"
    name="facets[<?php echo $facet->name; ?>][id]"
    value="<?php echo $facet->id; ?>"
  />

  <div
    class="facet-label"
    data-form-name="facets[<?php echo $facet->name; ?>][label]"
    data-revert-to="<?php echo $facet->getOriginalLabel(); ?>"
  >
    <?php echo $facet->label; ?>
  </div>

  </td>

  <?php foreach (array('is_indexed', 'is_facet') as $opt): ?>
    <td>
      <input

        type="checkbox"
        name="facets[<?php echo $facet->name; ?>][<?php echo $opt; ?>]"

        <?php if ($facet->$opt): ?>
          checked="checked"
        <?php endif; ?>

      />
    </td>
  <?php endforeach; ?>

</tr>
