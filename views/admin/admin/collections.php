<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2014 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>

<?php
queue_js_file('accordion');
queue_css_file('fields');

echo head(array(
    'title' => __('Solr Search | Collection Configuration'),
));
?>

<div id='solr-collections'>

<?php
echo $this->partial('admin/partials/navigation.php', array(
    'tab' => 'collections'
));
?>

<?php echo flash(); ?>

<p>The collections selected here will be <em>excluded</em> from indexing in 
Solr.</p>

<?php echo $form; ?>
</div>
