<?php if($this->pageCount > 1): ?>
<nav id="solr-nav" class="pagination">
    <?php if (isset($this->next)): ?>
        <a class="next" href="<?php echo html_escape($this->url(array('page' => $this->next), null, $_GET)); ?>"></a>
    <?php endif; ?>
</nav>
<?php endif; ?>

