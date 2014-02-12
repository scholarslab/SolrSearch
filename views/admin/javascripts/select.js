
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

jQuery(function($) {

  var selects = $('.group-sel-all');

  selects.each(function() {

    var allChecked = true;
    $($(this).attr('data-target')).each(function() {
      allChecked &= $(this).is(':checked');
    });

    $(this).prop('checked', allChecked);

  });

  selects.change(function(event) {
    $($(this).attr('data-target')).prop('checked', $(this).is(':checked'));
  });

});
