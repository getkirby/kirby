<h1>
  <a href="<?= url() ?>"><?= $site->title() ?></a>
</h1>

<nav>
  <?php foreach ($site->children()->listed() as $item): ?>
  <?= $item->title()->link() ?>
  <?php endforeach ?>
</nav>

<main>
  <h2><?= $page->title() ?></h2>
  <?= $page->text() ?>
</main>
