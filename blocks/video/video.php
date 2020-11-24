<?php if ($block->url()->isNotEmpty()): ?>
<figure<?= attr(['class' => $block->class()], ' ') ?>>
  <?= video($block->url()) ?>
  <?php if ($block->caption()->isNotEmpty()): ?>
  <figcaption><?= $block->caption() ?></figcaption>
  <?php endif ?>
</figure>
<?php endif ?>
