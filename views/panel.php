<!DOCTYPE html>
<html>
<head>

  <meta charset="utf-8">
  <title>Kirby Panel</title>

  <base href="<?= $kirby->url('panel') ?>">
  <link rel="stylesheet" href="<?= $kirby->url('panel') ?>/assets/css/panel.css">

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
        index:  '<?= $kirby->url() ?>'
      }
    };
  </script>
  <script src="<?= $kirby->url('panel') ?>/assets/js/panel.js"></script>

</body>
</html>
