<?php if ($files->isNotEmpty()): ?>
<figure <?= attr($attrs) ?>>
  <ul>
    <?php foreach ($files as $image): ?>
    <li>
      <?= $image ?>
    </li>
    <?php endforeach ?>
  </ul>
  <?php if ($caption->isNotEmpty()): ?>
  <figcaption>
    <?= $caption ?>
  </figcaption>
  <?php endif ?>
</figure>
<?php endif ?>
