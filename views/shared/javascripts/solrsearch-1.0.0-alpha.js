jQuery(document).ready(function() {

  jQuery('.solr_facets h3').click(function() {
    jQuery(this).next().toggle();
    return false;
  }).next().hide();

});
