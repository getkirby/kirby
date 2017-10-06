<?php

/*

The $flip parameter can be passed to the snippet to flip
prev/next items visually:

```
<?php snippet('prevnext', ['flip' => true]) ?>
```

Learn more about snippets and parameters at:
https://getkirby.com/docs/templates/snippets

*/

$directionPrev = @$flip ? 'right' : 'left';
$directionNext = @$flip ? 'left'  : 'right';

if($page->hasNextVisible() || $page->hasPrevVisible()): ?>
  <nav class="pagination <?= !@$flip ?: ' flip' ?> wrap cf">

    <?php if($page->hasPrevVisible()): ?>
      <a class="pagination-item <?= $directionPrev ?>" href="<?= $page->prevVisible()->url() ?>" rel="prev" title="<?= $page->prevVisible()->title()->html() ?>">
        <?= svg("assets/images/arrow-{$directionPrev}.svg") ?>
      </a>
    <?php else: ?>
      <span class="pagination-item <?= $directionPrev ?> is-inactive">
        <?= svg("assets/images/arrow-{$directionPrev}.svg") ?>
      </span>
    <?php endif ?>

    <?php if($page->hasNextVisible()): ?>
      <a class="pagination-item <?= $directionNext ?>" href="<?= $page->nextVisible()->url() ?>" rel="next" title="<?= $page->nextVisible()->title()->html() ?>">
        <?= svg("assets/images/arrow-{$directionNext}.svg") ?>
      </a>
    <?php else: ?>
      <span class="pagination-item <?= $directionNext ?> is-inactive">
        <?= svg("assets/images/arrow-{$directionNext}.svg") ?>
      </span>
    <?php endif ?>

  </nav>
<?php endif ?>
