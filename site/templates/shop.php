<?php snippet('header') ?>

<main>
  <?php snippet('intro') ?>

  <ul class="shop">
    <?php foreach ($page->children()->listed() as $product): ?>
    <li>
      <figure class="product">
        <a href="<?= $product->link() ?>">
          <?= $product->image()->crop(500) ?>
          <figcaption class="text">
            <h2 class="product-title"><?= $product->title() ?></h2>
            <p class="product-price">€ <?= $product->price() ?></p>
          </figcaption>
        </a>
      </figure>
    </li>
    <?php endforeach ?>
  </ul>
</main>

<?php snippet('footer') ?>
