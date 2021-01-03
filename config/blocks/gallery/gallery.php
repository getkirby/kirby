<?php /** @var \Kirby\Cms\Block $block */ ?>
<figure>
  <ul>
    <?php foreach ($block->images()->toFiles() as $image): ?>
    <li>
      <?= $image ?>
    </li>
    <?php endforeach ?>
  </ul>
</figure>
