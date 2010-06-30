<?php header('Content-Type: text/html; charset=utf-8'); ?>

<?php head(array('title' => 'Browse', 'bodyclass' => 'page')); ?>
<div id="primary">
	<h1>Browse</h1>
	<div class="item-list">
		<?php
		// display results
		if ($results)
		{
		?>
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
					    foreach ($doc as $field => $value)
					    {
					    $fieldSplit = explode('_', $field);
					  
					?>
				
						<?php if ($field != 'id' && $field != 'title' && in_array($fieldSplit[0], $displayFields)){ ?>					
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
					<?php
					    }
					?>
					</dl>
				</div>
			<?php
			}
			?>
		<?php
		}
		?>
	</div>
</div>

<?php echo foot(); ?>
