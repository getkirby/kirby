<?php if ($src->isNotEmpty()): ?>
<figure <?= attr($attrs) ?>>
  <?php if ($link->isNotEmpty()): ?>
  <a href="<?= $link->esc() ?>">
	<?= $img ?>
  </a>
  <?php else: ?>
	<?= $img ?>
  <?php endif ?>
  <?php if ($caption->isNotEmpty()): ?>
  <figcaption>
    <?= $caption ?>
  </figcaption>
  <?php endif ?>
</figure>
<?php endif ?>
