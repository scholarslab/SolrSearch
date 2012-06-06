<tr>
  <td class="element">
  <input name='<?php echo $facetId->getFullyQualifiedName() ?>' type='hidden' value='<?php echo $facetId->getValue() ?>' />
  <div id='<?php echo $id ?>' class='facetlabel'><?php echo $label->getValue() ?></div>
<script type="text/javascript">
tip(
    '#<?php echo $id ?>',
    '<?php echo $label->getFullyQualifiedName() ?>',
    '<?php echo $label->getAttrib('revertto') ?>'
);
</script>
</td>
