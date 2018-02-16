<h1><?= $page->title() ?></h1>

<ul>
  <?php foreach ($page->children()->filterBy('isPublic', true) as $method): ?>
  <li><?= $method->title()->link() ?></li>
  <?php endforeach ?>
</ul>
