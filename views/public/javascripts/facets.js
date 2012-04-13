jQuery(document).ready(function() {
  jQuery('.solr_facets h3').click(function() {

    jQuery(this).next().toggle(
      {
        animated: 'bounceslide',
        icons: { 'header': 'ui-icon-plus', 'headerSelected': 'ui-icon-minus' }
      }
    );
    return false;
  }).next().hide();
});

