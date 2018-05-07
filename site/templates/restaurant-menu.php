<?php snippet('header') ?>

<main class="restaurant-menu">
  <?php snippet('intro') ?>

  <?php foreach ($categories as $category): ?>
  <section class="dishes">
    <h2><?= $category ?></h2>
    <?php foreach ($page->$category()->toStructure() as $dish): ?>
    <article class="dish">
      <h3 class="dish-name"><?= $dish->dish() ?></h3>
      <p class="dish-description"><?= $dish->description() ?></p>
      <p class="dish-price">€ <?= $dish->price() ?></p>
    </article>
    <?php endforeach ?>
  </section>
  <?php endforeach ?>

</main>

<?php snippet('footer') ?>
