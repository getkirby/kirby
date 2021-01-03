<?php /** @var \Kirby\Cms\Block $block */ ?>
<blockquote>
  <?= $block->text() ?>
  <?php if ($block->citation()->isNotEmpty()): ?>
  <footer>
    <?= $block->citation() ?>
  </footer>
  <?php endif ?>
</blockquote>
