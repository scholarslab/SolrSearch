<?php if($this->pageCount > 1): ?>
<nav id="solr-nav">
   <?php if (isset($this->previous)): ?>
      <a class="previous" href="<?php echo html_escape($this->url(array('page' => $this->previous), null, $_GET)); ?>"></a>
   <?php endif; ?>

    <?php if (isset($this->next)): ?>
        <a class="next" href="<?php echo html_escape($this->url(array('page' => $this->next), null, $_GET)); ?>"></a>
    <?php endif; ?>
</nav>
<?php endif; ?>

