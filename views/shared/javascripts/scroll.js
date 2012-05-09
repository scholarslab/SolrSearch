jQuery(function($) {
  var $container = $('#results');

  $container.infinitescroll({
    //debug: true,
    animate: true,
    nextSelector: '#solr-nav a.next',
    navSelector: '#solr-nav',
    itemSelector: '.item',
    loading: {
      msgText: '<em>Loading next set of items...</em>',
      finishedMsg: '<em>You have reached the end of the results.</em>'
    }
  });

});
