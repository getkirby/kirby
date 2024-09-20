<?php
use Kirby\Cms\Html;

/** @var \Kirby\Cms\Block $block */
$caption = $block->caption();

if (
	$block->location() == 'kirby' &&
	$video = $block->video()->toFile()
) {
	$url   = $video->url();
	$attrs = array_filter([
		'autoplay'    => $block->autoplay()->toBool(),
		'controls'    => $block->controls()->toBool(),
		'loop'        => $block->loop()->toBool(),
		'muted'       => $block->muted()->toBool() || $block->autoplay()->toBool(),
		'playsinline' => $block->autoplay()->toBool(),
		'poster'      => $block->poster()->toFile()?->url(),
		'preload'     => $block->preload()->value(),
	]);
} else {
	$url = $block->url();
}
?>
<?php if ($video = Html::video($url, [], $attrs ?? [])): ?>
<figure>
  <?= $video ?>
  <?php if ($caption->isNotEmpty()): ?>
  <figcaption><?= $caption ?></figcaption>
  <?php endif ?>
</figure>
<?php endif ?>
