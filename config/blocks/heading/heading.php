<?php
/** @var \Kirby\Cms\Block $block */
$level = $block->level()->value();

// ensure a valid heading tag
if (in_array($level, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true) === false) {
	$level = 'h2';
}
?>
<<?= $level ?>><?= $block->text() ?></<?= $level ?>>
