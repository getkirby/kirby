<?php snippet('header') ?>

  <main class="main" role="main">

    <header class="wrap">
      <h1><?= $page->title()->html() ?></h1>
      <div class="intro text">
        <?= $page->intro()->kirbytext() ?>
      </div>
      <hr />
    </header>
      
    <div class="text wrap">
      <?= $page->text()->kirbytext() ?>
    </div>

  </main>

<?php snippet('footer') ?>