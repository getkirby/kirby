Before snippet
<?php snippet('simple', slots: true) ?>
Before rendering
<?= (new Kirby\Template\Template('plain'))->render() ?>
After rendering
<?php endsnippet() ?>
After snippet
