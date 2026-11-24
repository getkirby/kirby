<?php snippet('layout', slots: true) ?>

Some content from the template.

<?php snippet('plain') ?>

<?php snippet('slots', slots: true) ?>
<?php slot() ?>
Content from the template in the default slot of the snippet.
<?php endslot() ?>
<?php slot('footer') ?>
Content from the template in the footer slot of the snippet.
<?php endslot() ?>
<?php endsnippet() ?>

<?php snippet('with-layout') ?>
