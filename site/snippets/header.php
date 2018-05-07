<!doctype html>
<html lang="en">
<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title><?= $site->title() ?> | <?= $page->title() ?></title>

  <meta property="og:title" content="<?= $site->metaTitle() ?>">
  <meta property="og:description" content="<?= $site->metaDescription() ?>">

  <?= css(['assets/css/index.css', '@auto']) ?>

</head>
<body>

  <div class="page">
    <header class="header">
      <a class="logo" href="/">Løgø</a>

      <nav id="menu" class="menu">
        <label for="menu-toggle">Menu</label>
        <input id="menu-toggle" type="checkbox" />
        <span class="menu-dropdown">
          <?php foreach ($site->children()->listed() as $page): ?>
          <?= $page->title()->link() ?>
          <?php endforeach ?>
        </span>
      </nav>
    </header>

