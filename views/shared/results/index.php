<?php

/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2 cc=80; */

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>


<?php queue_css_file('results'); ?>
<?php echo head(array('title' => __('Solr Search')));?>


<h1><?php echo __('Search the Collection'); ?></h1>


<!-- Search form. -->
<div class="solr">
  <form id="solr-search-form">
    <input type="submit" value="Search" />
    <span class="float-wrap">
      <input type="text" name="q" value="<?php
        echo array_key_exists('q', $_GET) ? $_GET['q'] : '';
      ?>" />
    </span>
  </form>
</div>


<!-- Applied facets. -->
<div id="solr-applied-facets">

  <ul>

    <!-- Get the applied facets. -->
    <?php foreach (SolrSearch_Helpers_Facet::parseFacets() as $f): ?>
      <li>

        <!-- Facet label. -->
        <?php $label = SolrSearch_Helpers_Facet::nameToLabel($f[0]); ?>
        <span><?php echo $label; ?> > <?php echo $f[1]; ?></span>

        <!-- Remove link. -->
        <?php $url = SolrSearch_Helpers_Facet::removeFacet($f[0], $f[1]); ?>
        (<a href="<?php echo $url; ?>">remove</a>)

      </li>
    <?php endforeach; ?>

  </ul>

</div>


<!-- Facets. -->
<div id="solr-facets">
  <?php foreach ($results->facet_counts->facet_fields as $name => $facets): ?>

    <!-- Does the facet have any hits? -->
    <?php if (count(get_object_vars($facets))): ?>

      <!-- Facet label. -->
      <h5><?php echo SolrSearch_Helpers_Facet::nameTolabel($name); ?></h5>

      <ul>
        <!-- Facets. -->
        <?php foreach ($facets as $label => $num): ?>
          <li>

            <!-- Facet link. -->
            <?php $url = SolrSearch_Helpers_Facet::addFacet($name, $label); ?>
            <a href="<?php echo $url; ?>"><?php echo $label; ?></a>
            (<?php echo $num; ?>)

          </li>
        <?php endforeach; ?>
      </ul>

    <?php endif; ?>

  <?php endforeach; ?>
</div>


<!-- Results. -->
<div id="solr-results">

  <!-- Number found. -->
  <h3 id="solr-num-found">
    <?php echo $results->response->numFound; ?> results
  </h3>

  <?php foreach ($results->response->docs as $doc): ?>

    <!-- Document. -->
    <div class="solr-result">

      <!-- Header. -->
      <div class="solr-title">

        <!-- Title. -->
        <a href="<?php echo $doc->url; ?>">
          <?php echo is_array($doc->title) ? $doc->title[0] : $doc->title; ?>
        </a>

        <!-- Result type. -->
        <span class="result-type">(<?php echo $doc->resulttype; ?>)</span>

      </div>

      <!-- Highlighting. -->
      <?php if (get_option('solr_search_hl')): ?>
        <ul class="solr-hl">
          <?php foreach($results->highlighting->{$doc->id} as $field): ?>
            <?php foreach($field as $hl): ?>
              <li><?php echo $hl; ?></li>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

    </div>

  <?php endforeach; ?>

</div>


<?php echo pagination_links(); ?>
<?php echo foot();
