<?php

/**
 * @package     omeka
 * @subpackage  solr-search
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

?>

<?php if($this->pageCount > 1): ?>
<nav id="solr-nav" class="pagination">
    <?php if (isset($this->next)): ?>
        <a class="next" href="<?php echo html_escape($this->url(array('page' => $this->next), null, $_GET)); ?>"></a>
    <?php endif; ?>
</nav>
<?php endif; ?>

