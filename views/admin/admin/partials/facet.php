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
    name="facets[<?php echo $facet->name; ?>][id]"
    value="<?php echo $facet->id; ?>"
    type="hidden"
  />

  <span class="original-label">
    <?php echo $facet->getOriginalLabel(); ?>
  </span>

  </td>

  <td>
    <input
      name="facets[<?php echo $facet->name; ?>][label]"
      value="<?php echo htmlspecialchars($facet->label); ?>"
      type="text"
    />
  </td>

  <?php foreach (array('is_indexed', 'is_facet') as $opt): ?>
    <td>
      <input
        name="facets[<?php echo $facet->name; ?>][<?php echo $opt; ?>]"
        <?php if ($facet->$opt): ?>checked="checked"<?php endif; ?>
        type="checkbox"
      />
    </td>
  <?php endforeach; ?>

</tr>
