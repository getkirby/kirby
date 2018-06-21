<!DOCTYPE html>
<html>
<head>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title>Kirby Panel</title>

  <link rel="stylesheet" href="<?= $assetUrl ?>/css/fonts.css">
  <link rel="stylesheet" href="<?= $assetUrl ?>/css/app.css">
  <link rel="stylesheet" href="<?= $pluginCss ?>">
  <link rel="shortcut icon" href="<?= $assetUrl ?>/favicon.ico">

  <base href="<?= $panelUrl ?>">
</head>
<body>
  <?= $icons ?>
  <div id="app"></div>

  <script>window.panel = <?= json_encode($options, JSON_UNESCAPED_SLASHES) ?></script>

  <script src="<?= $assetUrl ?>/js/plugins.js"></script>
  <script src="<?= $assetUrl ?>/js/vendor.js"></script>
  <script src="<?= $pluginJs ?>"></script>
  <script src="<?= $assetUrl ?>/js/app.js"></script>

</body>
</html>
