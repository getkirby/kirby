<!doctype html>
<html lang="en">
<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title><?= $site->title()->html() ?> | <?= $page->title()->html() ?></title>
  <meta name="description" content="<?= $site->description()->html() ?>">

  <?= css('assets/css/index.css') ?>

</head>
<body>

  <header class="header wrap wide" role="banner">
    <div class="grid">

      <div class="branding column">
        <a href="<?= url() ?>" rel="home"><?= $site->title()->html() ?></a>
      </div>

      <?php snippet('menu') ?>

    </div>
  </header>
