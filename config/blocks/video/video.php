<?php if ($src->isNotEmpty()): ?>
<figure>
  <?= $embed ?>
  <?php if ($caption->isNotEmpty()): ?>
  <figcaption><?= $caption ?></figcaption>
  <?php endif ?>
</figure>
<?php endif ?>
