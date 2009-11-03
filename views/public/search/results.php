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
			<?php //echo solr_paginate($results, $query, $start, $limit, $page); ?>
			<div class="pagination"><?php echo pagination_links(); ?></div>
			<?php
			  // iterate result documents
			  foreach ($results->response->docs as $doc)
			  {
			?>	
				<div class="item">
					<ul>
		
					<?php
					    // iterate document fields / values
					    foreach ($doc as $field => $value)
					    {
					?>
						<?php if (is_array($value)){ foreach ($value as $multivalue) { ?>
							<li>
								<b><?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8'); ?>: </b>
								<?php echo htmlspecialchars($multivalue, ENT_NOQUOTES, 'utf-8'); ?>
							</li>
						<?php }} else { ?>
							<li>
								<b><?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8'); ?>: </b>
								<?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?>
							</li>
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
