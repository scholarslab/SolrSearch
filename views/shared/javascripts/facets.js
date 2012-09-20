jQuery(document).ready(function() {

  jQuery('.solr_facets .facet').addClass('clicker').click(function() {
    jQuery(this).toggleClass('active');
    jQuery(this).next().toggle();
    return false;
  }).next().hide();

});
