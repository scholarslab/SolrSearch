jQuery(function($) {
  var $container = $('#results');

  $container.infinitescroll({
    debug: true,
    animate: true,
    nextSelector: '#solr-nav a.next',
    navSelector: '#solr-nav',
    itemSelector: '.item',
    msgText: '<em>Loading next set of items...</em>',
    finishedMsg: '<em>You have reached the end of the results.</em>'
  }, function(newElements) {
    window.console && console.log('context: ', this);
    window.console && console.log('returned: ', newElements);
  });

});
