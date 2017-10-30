<?php if($pagination->hasPages()): ?>
  <nav class="pagination wrap cf">

    <?php if($pagination->hasPrevPage()): ?>
      <a class="pagination-item left" href="<?= $pagination->prevPageUrl() ?>" rel="prev" title="newer articles">
        <?= svg('assets/images/arrow-left.svg') ?>
      </a>
    <?php else: ?>
      <span class="pagination-item left is-inactive">
        <?= svg('assets/images/arrow-left.svg') ?>
      </span>
    <?php endif ?>

    <?php if($pagination->hasNextPage()): ?>
      <a class="pagination-item right" href="<?= $pagination->nextPageUrl() ?>" rel="next" title="older articles">
        <?= svg('assets/images/arrow-right.svg') ?>
      </a>
    <?php else: ?>
      <span class="pagination-item right is-inactive">
        <?= svg('assets/images/arrow-right.svg') ?>
      </span>
    <?php endif ?>

  </nav>
<?php endif ?>
