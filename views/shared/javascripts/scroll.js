
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

jQuery(document).ready(function() {
  jQuery('#solr-nav').hide();
});

jQuery(function($) {

  var $container = $('#results');

  $container.infinitescroll({
    animate: true,
    nextSelector: '#solr-nav a.next',
    navSelector: '#solr-nav',
    itemSelector: '.item',
    loading: {
      msgText: '<p><em>Loading next set of items...</em></p>',
      finishedMsg: '<p><em>You have reached the end of the results.</em></p>'
    }
  });

});
