<?php

use Kirby\Toolkit\Html;

/**
 * @var array<string, string> $browsers
 */
$list = [];

foreach ($browsers as $browser => $version) {
	$list[] = Html::encode($browser . ' ' . $version);
}

?>
<?php include __DIR__ . '/snippets/header.php' ?>

  <p class="notice">
    We are really sorry, but your browser does not support
    all features required for the Kirby Panel.
  </p>

  <div class="admin-advice">
    <p>
      The Panel needs one of these browsers or higher:<br>
      <strong><?= implode(', ', $list) ?></strong>
    </p>
    <p>
      Please update your browser or switch to a different one.
    </p>
  </div>

<?php include __DIR__ . '/snippets/footer.php' ?>
