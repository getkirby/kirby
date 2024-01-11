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
		'controls' => true,
		'poster'   => $block->poster()->toFile()?->url()
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
