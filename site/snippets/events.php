<?php if ($events->count() > 0): ?>
<ul>
  <?php foreach ($events as $event): ?>
  <li class="event">
    <a href="<?= $event->link() ?>">
      <header>
        <h3><?= $event->title() ?></h3>
        <time><?= $event->date('d.m.', 'from') ?> - <?= $event->date('d.m.Y', 'to') ?></time>
      </header>
      <figure><?= $event->image() ?></figure>
      <footer><?= $event->location() ?></footer>
    </a>
  </li>
  <?php endforeach ?>
</ul>
<?php endif ?>
