<?php snippet('header') ?>

<main class="blog">
  <?php snippet('intro') ?>

  <?php foreach ($kirby->collection('articles') as $article): ?>
  <?php snippet('article', ['article' => $article]) ?>
  <?php endforeach ?>
</main>

<?php snippet('footer') ?>
