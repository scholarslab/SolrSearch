<?php
  $pageTitle = __('Browse Items'); //TODO: Should this be browse items?
  head(array('title' => $pageTitle, 'id' => 'items', 'bodyclass' => 'browse'));
?>

<div id="primary" class="solr_results results">
  <h1><?php echo $pageTitle; ?> </h1>

  <div id="solr_results" class="item-list">
    <div id="solr_search" class="search solr_remove_facets">
      <?php //TODO: Fix button on this... ?>
      <?php echo SolrSearch_ViewHelpers::createSearchForm(); ?>
    </div>
    <div id="appliedParams">
      <h3>You searched for:</h3>
      <?php echo SolrSearch_QueryHelpers::removeFacets(); ?>
    </div>
    <div class="resultLine">
      <span class="results">
        <strong><?php echo __('%s', $results->response->numFound); ?></strong> results
      </span>
      <nav class="pagination">
        <?php echo pagination_links(); ?>
      </nav>
      <div class="solr_sort_form"><?php echo SolrSearch_ViewHelpers::createSortForm(); ?></div>

    </div>

    <?php if(!empty($facets)): ?>
      <?php $query = SolrSearch_QueryHelpers::getParams(); ?>
      <div class="solr_facets_container">
        <h2>Limit your search</h2>
        <div class="solr_facets">
        <?php foreach($results->facet_counts->facet_fields as $facet => $values): ?>
        <h3><?php echo SolrSearch_QueryHelpers::parseFacet($facet); ?></h3>
        <ul>
					<?php foreach($values as $label => $count): ?>
            <li><?php echo SolrSearch_QueryHelpers::createFacetHtml($query, $facet, $label, $count); ?></li>
					<?php endforeach; ?>
        </ul>
        <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div id="results">
    <?php foreach($results->response->docs as $doc): ?>
    <div class="item" id="solr_<?php echo $doc->__get('id'); ?>">
      <div class="details">
        <div class="title">
          <h2><?php echo SolrSearch_ViewHelpers::createResultLink($doc); ?></h2>
        </div>

        <?php if($results->responseHeader->params->hl == true): ?>
        <div class="solr_highlight">
          <p><?php echo SolrSearch_ViewHelpers::displaySnippets($doc->id, $results->highlighting); ?></p>
        </div>
        <?php endif; ?>

        <?php $tags = $doc->__get('tag'); ?>
        <?php if($tags): ?>
          <div class="tags">
            <strong>Tags:</strong>
            <?php echo SolrSearch_ViewHelpers::tagsToStrings($tags); ?>
          </div>
        <?php endif; ?>

        <?php $image = $doc->__get('image');?>
        <?php if($image): ?>
        <div class="image">
          <?php echo SolrSearch_ViewHelpers::createResultImgHtml($image, SolrSearch_ViewHelpers::getDocTitle($doc)); ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
  echo foot();
