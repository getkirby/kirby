Before snippet
<?php snippet('simple', slots: true) ?>
Before rendering
<?= (new Kirby\Template\Template('with-layout'))->render() ?>
After rendering
<?php endsnippet() ?>
After snippet
