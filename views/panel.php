<!DOCTYPE html>
<html>
<head>

  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title>Kirby Panel</title>

  <base href="<?= $kirby->url('panel') ?>">
  <link rel="stylesheet" href="<?= $kirby->url('panel') ?>/assets/css/panel.css">
  <link rel="stylesheet" href="<?= $kirby->url('index') ?>/assets/css/panel.css">

</head>
<body>

  <main>
    <kirby-panel></kirby-panel>
  </main>

  <script>
    var panel = {
      config: {
        api:    '<?= $kirby->url('api') ?>',
        assets: '<?= $kirby->url('panel') ?>/assets',
        index:  '<?= $kirby->url() ?>',
        // TODO: enable/disable via options
        debug:  true
      }
    };
  </script>
  <script src="<?= $kirby->url('panel') ?>/assets/js/vendor.js"></script>
  <script src="<?= $kirby->url('panel') ?>/assets/js/registry.js"></script>
  <script src="<?= $kirby->url('panel') ?>/assets/js/ui.js"></script>
  <script src="<?= $kirby->url('index') ?>/assets/js/panel.js"></script>
  <script src="<?= $kirby->url('panel') ?>/assets/js/panel.js"></script>

</body>
</html>
