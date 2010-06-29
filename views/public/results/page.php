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
					<ul>
		
					<?php
					    // iterate document fields / values
					    foreach ($doc as $field => $value)
					    {
					?>
				
						<?php if ($field != 'id' && $field != 'title'){ ?>						
							<?php if (is_array($value)){ foreach ($value as $multivalue) { ?>
								<li>
									<b><?php echo solr_search_element_lookup($field) ?>: </b>
									<?php echo htmlspecialchars($multivalue, ENT_NOQUOTES, 'utf-8'); ?>
								</li>
							<?php }} else { ?>
								<li>
									<b><?php echo solr_search_element_lookup($field) ?>: </b>
									<?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?>
								</li>
							<?php } ?>
						<?php } ?>
					<?php
					    }
					?>
					</ul>
					<dl>
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
