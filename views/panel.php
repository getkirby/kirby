<?php

use Kirby\Toolkit\Html;

/**
 * @var \Kirby\Cms\App $kirby
 * @var string $icons
 * @var array<string, mixed> $assets
 * @var array<string, mixed> $fiber
 * @var string $panelUrl
 * @var string $nonce
 */ ?>
<!DOCTYPE html>
<html>
<head>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <meta name="referrer" content="same-origin">

  <title>Kirby Panel</title>

  <script nonce="<?= $nonce ?>">
    if (
        !window.CSS ||
        window.CSS.supports("display", "grid") === false ||
        !window.fetch
    ) {
      window.location.href = "<?= $panelUrl ?>browser";
    }
  </script>

  <?php foreach ($assets['css'] as $css): ?>
  <link nonce="<?= $nonce ?>" rel="stylesheet" href="<?= $css ?>">
  <?php endforeach ?>

  <?php foreach ($assets['icons'] as $icon): ?>
  <?= Html::tag('link', null, $icon) ?>
  <?php endforeach ?>

  <?php foreach ($assets['js'] as $js): ?>
  <?php if (($js['type'] ?? null) === 'module'): ?>
  <link rel="modulepreload" href="<?= $js['src'] ?>">
  <?php endif ?>
  <?php endforeach ?>

  <base href="<?= $panelUrl ?>">
</head>
<body>
  <div id="app"></div>

  <noscript>
    Please enable JavaScript in your browser
  </noscript>

  <?= $icons ?>

  <script nonce="<?= $nonce ?>">
    // Fiber setup
    window.fiber = <?= json_encode($fiber) ?>;
  </script>

  <?php foreach ($assets['js'] as $key => $js): ?>
  <?php if ($key === 'index'): ?>
  <script type="module" nonce="<?= $nonce ?>">
    <?= $assets['plugin-imports'] ?>
    import('<?= $js['src'] ?>')
  </script>
  <?php else: ?>
  <?= Html::tag('script', '', $js) . PHP_EOL ?>
  <?php endif ?>
  <?php endforeach ?>

</body>
</html>
