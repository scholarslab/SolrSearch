
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

jQuery(function($) {

  $('.group-sel-all').change(function(event) {
    var checkbox = $(this);
    $(checkbox.attr('data-target')).prop('checked',
      checkbox.is(':checked')
    );
  });

});
