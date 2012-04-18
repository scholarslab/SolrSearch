<?php header('Content-Type: text/html; charset=utf-8'); ?>

<?php head(array('title' => 'Browse', 'bodyclass' => 'page')); ?>
<div id="primary">
	<div class="<?php if (!empty($facets)){ echo 'solr_results'; } ?>">
		<h1>Browse</h1>
		<div class="item-list">
			<?php
			// display results
			if ($results)
			{
			?>
				<div class="solr_remove_facets"><h2>Current Query</h2><ul><?php echo SolrSearch_QueryHelpers::removeFacets(); ?></ul></div>
				<div class="solr_sort">
					<h4>Total Results: <?php echo $results->response->numFound; ?></h4>
					<div class="solr_sort_form"><?php echo SolrSearch_ViewHelpers::createSortForm(); ?></div>
				</div>
				<div class="pagination"><?php echo pagination_links(); ?></div>
				
				<?php
				  // iterate result documents
				  foreach ($results->response->docs as $doc)
				  {
				?>	
					<div class="item">
                        <h3><?php echo SolrSearch_ViewHelpers::createResultLink($doc); ?></h3>
						
						<dl class="solr_result_doc">
						<?php
						    // iterate document fields / values
						    foreach ($doc as $field => $value) { ?>					
							<?php if ($field != 'id' && $field != 'title' && $field != 'image' && $field){ ?>	
								<?php if (is_array($value)){ foreach ($value as $multivalue) { ?>
									<div>
										<dt>
											<?php if (strstr($field, '_')) { ?>
												<?php echo SolrSearch_ViewHelpers::lookupElement($field) ?>
											<?php } else { echo ucwords($field); }?>										
										</dt>
										<dd><?php echo htmlspecialchars($multivalue, ENT_NOQUOTES, 'utf-8'); ?></dd>
									</div>
								<?php }} else { ?>
									<div>
										<dt>	
											<?php if (strstr($field, '_')) { ?>
												<?php echo SolrSearch_ViewHelpers::lookupElement($field) ?>
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
									<a class="solr_search_image" href="<?php echo SolrSearch_ViewHelpers::getImagePath('fullsize', $multivalue) ?>">
										<img alt="<?php echo SolrSearch_ViewHelpers::getDocTitle($doc); ?>" src="<?php echo SolrSearch_ViewHelpers::getImagePath('square_thumbnail', $multivalue) ?>"/>
									</a>
								<?php }} else { ?>
									<a class="solr_search_image" href="<?php echo SolrSearch_ViewHelpers::getImagePath('fullsize', $value) ?>">
										<img alt="<?php echo SolrSearch_ViewHelpers::getDocTitle($doc); ?>" src="<?php echo SolrSearch_ViewHelpers::getImagePath('square_thumbnail', $value) ?>"/>
									</a>
								<?php } ?>
							<?php } ?>
						<?php } ?>
						
						<?php 
						//display highlighting, if applicable
						if ($results->responseHeader->params->hl == 'true'){ ?>
							<div class="solr_highlight">
								<?php echo SolrSearch_ViewHelpers::displaySnippets($doc->id, $results->highlighting); ?>
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
        <?php $query = SolrSearch_QueryHelpers::getParams(); ?>
		<div class="solr_facets">
			<h2>Facets</h2>
			<?php foreach ($results->facet_counts->facet_fields as $facet => $values){ ?>
					<h3><?php if (strstr($facet, '_')) { ?>
							<?php echo SolrSearch_ViewHelpers::lookupElement($facet); ?>		
						<?php } else { echo ucwords($facet); }?>
					</h3>	
					
				<ul>
					<?php foreach($values as $label => $count){ ?>
						<li><?php echo SolrSearch_QueryHelpers::createFacetHtml($query, $facet, $label, $count); ?></li>
					<?php } ?>
				</ul>
			<?php } ?>
		</div>
	<?php } ?>	
</div>

<?php echo foot(); ?>
