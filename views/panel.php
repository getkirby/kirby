<!DOCTYPE html>
<html>
<head>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title>Kirby Panel</title>

  <link rel="stylesheet" href="<?= $assetUrl ?>/css/app.css">
  <link rel="stylesheet" href="<?= $pluginCss ?>">

  <?php if (isset($config['css'])) : ?>
    <link rel="stylesheet" href="<?= Url::to($config['css']) ?>">
  <?php endif ?>

  <link rel="apple-touch-icon" href="<?= $assetUrl ?>/apple-touch-icon.png" />
  <link rel="shortcut icon" href="<?= $assetUrl ?>/favicon.png">

  <base href="<?= $panelUrl ?>">
</head>
<body>
  <?= $icons ?>
  <div id="app"></div>

  <noscript>
    Please enable JavaScript in your browser
  </noscript>

  <script>window.panel = <?= json_encode($options, JSON_UNESCAPED_SLASHES) ?></script>

  <script src="<?= $assetUrl ?>/js/plugins.js" defer></script>
  <script src="<?= $assetUrl ?>/js/vendor.js" defer></script>
  <script src="<?= $pluginJs ?>" defer></script>
  <?php if (isset($config['js'])) : ?>
    <script src="<?= Url::to($config['js']) ?>" defer></script>
  <?php endif ?>
  <script src="<?= $assetUrl ?>/js/app.js" defer></script>

</body>
</html>
