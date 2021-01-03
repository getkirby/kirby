<?php
/**
 * @var \Kirby\Cms\App $kirby
 * @var array<string, mixed> $config
 * @var string $assetUrl
 * @var string|false $customCss
 * @var string $icons
 * @var string $pluginCss
 * @var string $pluginJs
 * @var string $panelUrl
 * @var string $nonce
 * @var array<string, mixed> $options
 */ ?>
<!DOCTYPE html>
<html>
<head>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <meta name="referrer" content="same-origin">

  <title>Kirby Panel</title>

  <link nonce="<?= $nonce ?>" rel="stylesheet" href="<?= $assetUrl ?>/css/app.css">
  <link nonce="<?= $nonce ?>" rel="stylesheet" href="<?= $pluginCss ?>">

  <?php if ($customCss) : ?>
  <link nonce="<?= $nonce ?>" rel="stylesheet" href="<?= $customCss ?>">
  <?php endif ?>

  <link nonce="<?= $nonce ?>" rel="apple-touch-icon" href="<?= $assetUrl ?>/apple-touch-icon.png" />
  <link nonce="<?= $nonce ?>" rel="icon" href="<?= $assetUrl ?>/favicon.svg" type="image/svg+xml">
  <link nonce="<?= $nonce ?>" rel="alternate icon" href="<?= $assetUrl ?>/favicon.png" type="image/png">

  <base href="<?= $panelUrl ?>">
</head>
<body>
  <svg aria-hidden="true" class="k-icons" xmlns="http://www.w3.org/2000/svg" overflow="hidden" nonce="<?= $nonce ?>">
    <defs />
  </svg>

  <div id="app"></div>

  <noscript>
    Please enable JavaScript in your browser
  </noscript>

  <?= $icons ?>

  <script nonce="<?= $nonce ?>">window.panel = <?= json_encode($options, JSON_UNESCAPED_SLASHES) ?></script>
  <script nonce="<?= $nonce ?>" src="<?= $assetUrl ?>/js/plugins.js" defer></script>
  <script nonce="<?= $nonce ?>" src="<?= $assetUrl ?>/js/vendor.js" defer></script>
  <script nonce="<?= $nonce ?>" src="<?= $pluginJs ?>" defer></script>
  <?php if (isset($config['js'])) : ?>
    <script nonce="<?= $nonce ?>" src="<?= Url::to($config['js']) ?>" defer></script>
  <?php endif ?>
  <script nonce="<?= $nonce ?>" src="<?= $assetUrl ?>/js/app.js" defer></script>

</body>
</html>
