<?php
  $pageTitle = __('Browse Items'); //TODO: Should this be browse items?
  head(array('title' => $pageTitle, 'id' => 'items', 'bodyclass' => 'browse'));
?>

<div id="primary" class="solr_results">
  <h1><?php echo $pageTitle; ?> <?php echo __('(%s total)', $results->response->numFound); ?></h1>

  <div id="pagination-top" class="pagination">
    <?php echo pagination_links(); ?>
  </div>

  <div class="item-list">
    <div id="solr_search" class="search solr_remove_facets">
      <?php //TODO: Fix button on this... ?>
      <?php echo solr_search_form(); ?>
    </div>
    <div id="appliedParams">
      <?php echo solr_search_remove_facets(); ?>
    </div>

    <p><?php //TODO: discuss spelling queries and where to push the sort-by method ?></p>


    <hr />

    </div>
    <div class="solr_sort">
      <h4>Total Results: <?php echo $results->response->numFound; ?></h4>
      <div class="solr_sort_form"><?php echo solr_search_sort_form(); ?></div>
    </div>

    <?php foreach($results->response->docs as $doc): ?>
      <div class="item hentry">
        <div class="item-meta">
          <h2><?php echo solr_search_result_link($doc); ?></h2>
          <?php// foreach($doc as $field => $value): ?>
            <?ph//p $whitelist = array('id', 'title', 'image'); ?>
            <? //TODO: do this better ?>
            <?ph//p if(!in_array($field, $whitelist)): ?>
              <?php// if(is_array($value)): ?>
                <?php // TODO: split this array better; ?>
                <?php// foreach($value as $multivalue): ?>
<?php// echo ucwords($field); ?>
                <?php// endforeach; ?>
              <?php// endif; ?>
              <?php // TODO: write helper to parse value strings ?>
              <div class="<?php echo $field; ?>"<?php echo $value; ?></div>
            <?php //endif; ?>
          <?php //endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

  </div>
</div>

<hr/>
<div id="primary">
	<div class="<?php if (!empty($facets)){ echo 'solr_results'; } ?>">
		<h1>Browse</h1>
		<div class="item-list">
			<?php
			// display results
			if ($results)
			{
			?>
				<div class="solr_remove_facets"><h2>Current Query</h2><ul><?php echo solr_search_remove_facets(); ?></ul></div>
				<div class="solr_sort">
					<h4>Total Results: <?php echo $results->response->numFound; ?></h4>
					<div class="solr_sort_form"><?php echo solr_search_sort_form(); ?></div>
				</div>
				<div class="pagination"><?php echo pagination_links(); ?></div>
				
				<?php
				  // iterate result documents
				  foreach ($results->response->docs as $doc)
				  {
				?>	
					<div class="item">
						<h3><?php echo solr_search_result_link($doc); ?></h3>
						
						<dl class="solr_result_doc">
						<?php
						    // iterate document fields / values
						    foreach ($doc as $field => $value) { ?>					
							<?php if ($field != 'id' && $field != 'title' && $field != 'image' && $field){ ?>	
								<?php if (is_array($value)){ foreach ($value as $multivalue) { ?>
									<div>
										<dt>
											<?php if (strstr($field, '_')) { ?>
												<?php echo solr_search_element_lookup($field) ?>
											<?php } else { echo ucwords($field); }?>										
										</dt>
										<dd><?php echo htmlspecialchars($multivalue, ENT_NOQUOTES, 'utf-8'); ?></dd>
									</div>
								<?php }} else { ?>
									<div>
										<dt>	
											<?php if (strstr($field, '_')) { ?>
												<?php echo solr_search_element_lookup($field) ?>
											<?php } else { echo ucwords($field); }?>
										</dt>
										<dd><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?></dd>
									</div>
								<?php } ?>
							<?php } ?>
						<?php } ?>						
						</dl>
						<?php
						// display images last
						foreach ($doc as $field=>$value) { ?>
							<?php if ($field == 'image') {?>
								<?php if (is_array($value)){ foreach ($value as $multivalue) { ?>
									<a class="solr_search_image" href="<?php echo solr_search_image_path('fullsize', $multivalue) ?>">
										<img alt="<?php echo solr_search_doc_title($doc); ?>" src="<?php echo solr_search_image_path('square_thumbnail', $multivalue) ?>"/>
									</a>
								<?php }} else { ?>
									<a class="solr_search_image" href="<?php echo solr_search_image_path('fullsize', $value) ?>">
										<img alt="<?php echo solr_search_doc_title($doc); ?>" src="<?php echo solr_search_image_path('square_thumbnail', $value) ?>"/>
									</a>
								<?php } ?>
							<?php } ?>
						<?php } ?>
						
						<?php 
						//display highlighting, if applicable
						if ($results->responseHeader->params->hl == 'true'){ ?>
							<div class="solr_highlight">
								<?php echo solr_search_display_snippets($doc->id, $results->highlighting); ?>
							</div>
						<?php } ?>					
					</div>
				<?php } ?>
			<?php } ?>
		</div>
		<div class="pagination"><?php echo pagination_links(); ?></div>
	</div>
	<?php //display facets ?>
	<?php if (!empty($facets)){ ?>
        <?php $query = solr_search_get_params(); ?>
		<div class="solr_facets">
			<h2>Facets</h2>
			<?php foreach ($results->facet_counts->facet_fields as $facet => $values){ ?>
					<h3><?php if (strstr($facet, '_')) { ?>
							<?php echo solr_search_element_lookup($facet); ?>		
						<?php } else { echo ucwords($facet); }?>
					</h3>	
					
				<ul>
					<?php foreach($values as $label => $count){ ?>
						<li><?php echo solr_search_facet_link($query, $facet, $label, $count); ?></li>
					<?php } ?>
				</ul>
			<?php } ?>
		</div>
	<?php } ?>	
</div>

<?php echo foot(); ?>
