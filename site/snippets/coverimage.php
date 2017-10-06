<?php if($image = $item->coverimage()->toFile()): ?>
  <figure>
    <img src="<?= $image->url() ?>" alt="" />
  </figure>
<?php endif ?>
