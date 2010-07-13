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
			<h4 class="solr_search_numFound">Total Results: <?php echo $results->response->numFound; ?></h4>
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
							<?php if ($field != 'id' && $field != 'title'){ ?>					
								<?php if (is_array($value)){ foreach ($value as $multivalue) { ?>
									<div>
										<dt><?php echo solr_search_element_lookup($field) ?></dt>
										<dd><?php echo htmlspecialchars($multivalue, ENT_NOQUOTES, 'utf-8'); ?></dd>
									</div>
								<?php }} else { ?>
									<div>
										<dt><?php echo solr_search_element_lookup($field) ?></dt>
										<dd><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?></dd>
									</div>
								<?php } ?>
							<?php } ?>
						<?php } ?>
						</dl>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
	</div>
	<?php //display facets ?>
	<?php if (!empty($facets)){ ?>
		<div class="solr_facets">
			<h2>Facets</h2>
			<?php foreach ($results->facet_counts->facet_fields as $facet => $values){ ?>
					<h3><?php echo solr_search_element_lookup($facet); ?></h3>			
				<ul>
					<?php foreach($values as $label => $count){ ?>
						<li><?php echo solr_search_facet_link($facet,$label,$count); ?></li>
					<?php } ?>
				</ul>
			<?php } ?>
		</div>
	<?php } ?>
</div>

<?php echo foot(); ?>
