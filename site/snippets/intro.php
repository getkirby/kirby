<header class="intro">
  <h1><?= $page->title() ?></h1>
  <?php if ($page->intro()->isNotEmpty()): ?>
  <div class="intro-text text">
    <?= $page->intro()->kt() ?></p>
  </div>
  <?php endif ?>
</header>
