<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <meta name="referrer" content="same-origin">

  <title>Kirby Panel</title>

  <?php foreach ($assets['css'] as $css): ?>
  <link nonce="<?= $nonce ?>" rel="stylesheet" href="<?= $css ?>">
  <?php endforeach ?>

  <?php foreach ($assets['icons'] as $rel => $icon): ?>
  <link nonce="<?= $nonce ?>" rel="<?= $rel ?>" href="<?= $icon['url'] ?>" type="<?= $icon['type'] ?>">
  <?php endforeach ?>

  <base href="<?= $panelUrl ?>">

  <script>
    if (!window.CSS || window.CSS.supports("display", "grid") === false || !window.fetch) {
      window.location.href = "<?= $panelUrl ?>browser";
    }
  </script>

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

  <script nonce="<?= $nonce ?>">
    // Panel state
    window.panel = <?= json_encode($inertia['props'], JSON_UNESCAPED_SLASHES) ?>;

    // Inertia setup
    window.inertia = {
        component: '<?= $inertia['component'] ?>',
        props: JSON.parse(JSON.stringify(window.panel)),
        url: '<?= $inertia['url'] ?>',
        version: '<?= $inertia['version'] ?>',
    };
  </script>

  <?php foreach ($assets['js'] as $js): ?>
  <script nonce="<?= $nonce ?>" src="<?= $js ?>"></script>
  <?php endforeach ?>

</body>
</html>
