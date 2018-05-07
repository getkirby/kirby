<?php snippet('header') ?>
<main>
  <?php snippet('intro') ?>

  <div class="events">

    <?php if ($future->count()): ?>
    <div class="events-list">
      <h2>Upcoming</h2>
      <?php snippet('events', ['events' => $future]) ?>
    </div>
    <?php endif ?>

    <?php if ($current->count()): ?>
    <div class="events-list">
      <h2>Current events</h2>
      <?php snippet('events', ['events' => $current]) ?>
    </div>
    <?php endif ?>

    <?php if ($past->count()): ?>
    <div class="events-list">
      <h2>Past events</h2>
      <?php snippet('events', ['events' => $past]) ?>
    </div>
    <?php endif ?>
  </div>

</main>

<?php snippet('footer') ?>
