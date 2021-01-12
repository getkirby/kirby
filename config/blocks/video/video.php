<?php /** @var \Kirby\Cms\Block $block */ ?>
<?php if ($video = video($block->url())): ?>
<figure>
  <?= $video ?>
  <?php if ($block->caption()->isNotEmpty()): ?>
  <figcaption><?= $block->caption() ?></figcaption>
  <?php endif ?>
</figure>
<?php endif ?>
