<?php

/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=80; */

/**
 * @package     omeka
 * @subpackage  fedora-connector
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>

<tr>
  <td class="element">

  <input
    name="<?php echo $facetId->getFullyQualifiedName() ?>"
    value="<?php echo $facetId->getValue() ?>"
    type="hidden" />

  <div id="<?php echo $id ?>" class="acetlabel"><?php echo $label->getValue() ?></div>

  <script type="text/javascript">
    tip(
      '#<?php echo $id ?>',
      '<?php echo $label->getFullyQualifiedName() ?>',
      '<?php echo $label->getAttrib('revertto') ?>'
    );
  </script>

  </td>
