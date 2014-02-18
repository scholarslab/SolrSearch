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

  <div id="facet-<?php echo $facet->id; ?>" class="facetlabel">
    <?php echo $facet->label; ?>
  </div>

  <script type="text/javascript">
    jQuery(function($) {
      $('#facet-<?php echo $facet->id; ?>').textinplace({
        form_name: 'facets[<?php echo $facet->name; ?>][label]',
        revert_to: '<?php echo $facet->getOriginalLabel(); ?>'
      });
    });
  </script>

  </td>

  <?php foreach (array('is_displayed', 'is_facet') as $column): ?>
    <td>
      <input

        name="facets[<?php echo $facet->name; ?>][options][]"
        value="<?php echo $column; ?>"
        type="checkbox"

        <?php if ($facet->$column): ?>
          checked="checked"
        <?php endif; ?>

      />
    </td>
  <?php endforeach; ?>

</tr>
