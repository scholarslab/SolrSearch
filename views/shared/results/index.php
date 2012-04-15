<?php
  $pageTitle = __('Browse Items'); //TODO: Should this be browse items?
  head(array('title' => $pageTitle, 'id' => 'items', 'bodyclass' => 'browse'));
?>

<div id="primary" class="solr_results results">
  <h1><?php echo $pageTitle; ?> </h1>

  <div id="solr_results" class="item-list">
    <div id="solr_search" class="search solr_remove_facets">
      <?php //TODO: Fix button on this... ?>
      <?php echo solr_search_form(); ?>
    </div>
    <div id="appliedParams">
      <h3>You searched for:</h3>
      <?php echo solr_search_remove_facet(); ?>
    </div>
    <div class="resultLine">
      <span class="results">
        <strong><?php echo __('%s', $results->response->numFound); ?></strong> results
      </span>
      <nav class="pagination">
        <?php echo pagination_links(); ?>
      </nav>
      <div class="solr_sort_form"><?php echo solr_search_sort_form(); ?></div>

    </div>

    <?php if(!empty($facets)): ?>
      <?php $query = solr_search_get_params(); ?>
      <div class="solr_facets_container">
        <h2>Limit your search</h2>
        <div class="solr_facets">
        <?php foreach($results->facet_counts->facet_fields as $facet => $values): ?>
        <h3><?php echo solr_search_parse_facet($facet); ?></h3>
        <ul>
					<?php foreach($values as $label => $count): ?>
						<li><?php echo solr_search_facet_link($query, $facet, $label, $count); ?></li>
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
          <h2><?php echo solr_search_result_link($doc); ?></h2>
        </div>

        <?php if($results->responseHeader->params->hl == true): ?>
        <div class="solr_highlight">
          <p><?php echo solr_search_display_snippets($doc->id, $results->highlighting); ?></p>
        </div>
        <?php endif; ?>

        <?php $tags = $doc->__get('tag'); ?>
        <?php if($tags): ?>
          <div class="tags">
            <strong>Tags:</strong>
            <?php echo solr_tags_as_string(); ?>
          </div>
        <?php endif; ?>

        <?php $image = $doc->__get('image');?>
        <?php if($image): ?>
        <div class="image">
          <?php echo solr_search_result_image($image, solr_search_doc_title($doc)); ?>
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
