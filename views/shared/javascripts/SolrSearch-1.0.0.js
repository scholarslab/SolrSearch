jQuery(document).ready(function() {

  jQuery('.solr_facets .facet').addClass('clicker').click(function() {
    jQuery(this).toggleClass('active');
    jQuery(this).next().toggle();
    return false;
  }).next().hide();

});


jQuery(document).ready(function() {
  jQuery('#solr-nav').hide();
});

jQuery(function($) {
  var $container = $('#results');

  $container.infinitescroll({
    //debug: true,
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
