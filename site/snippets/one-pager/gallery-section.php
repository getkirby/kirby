<ul>
  <?php foreach ($section->images() as $image): ?>
  <li>
    <figure>
      <?= $image->crop(600, 400) ?>
    </figure>
  </li>
  <?php endforeach ?>
</ul>
