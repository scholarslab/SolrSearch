<?php

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$limit = SOLR_ROWS;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : false;
if ($page == null){
	$page = 1;
	$start = 0;
}
else{
	$start = ($page - 1) * $limit;
}

$results = false;

if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  //require_once('Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service(SOLR_SERVER, SOLR_PORT, SOLR_CORE);
 

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }

  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {
    $results = $solr->search($query, $start, $limit, $page);
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
        // and then show a special message to the user but for this example
        // we're going to show the full exception
        die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>

<?php head(array('title' => 'Browse', 'bodyclass' => 'page')); ?>
<div id="primary">
	<h1>Browse</h1>
	<div class="item-list">
		<?php
		// display results
		if ($results)
		{
		?>
			<?php echo solr_paginate($results, $query, $start, $limit, $page); ?>
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
						<li><b><?php echo htmlspecialchars($field, ENT_NOQUOTES, 'utf-8'); ?>: </b><?php echo htmlspecialchars($value, ENT_NOQUOTES, 'utf-8'); ?></li>
					<?php
					    }
					?>
					</ul>
		
				<?php /* ?><dt>Date: </dt>
				<dd><?php echo htmlspecialchars($doc->date_display, ENT_NOQUOTES, 'utf-8'); ?></dd> <?php echo htmlspecialchars($field[1], ENT_NOQUOTES, 'utf-8'); ?><?php */ ?>		
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
