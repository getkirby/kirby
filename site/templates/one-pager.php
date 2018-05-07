<?php snippet('header') ?>

<main class="one-pager">
  <?php snippet('intro') ?>
  <?php foreach ($page->children()->listed() as $section): ?>
  <section class="section <?= $section->template() ?>">
    <?php snippet(str_replace("one-pager-", "one-pager/", $section->template()), ['section' => $section]) ?>
  </section>
  <?php endforeach ?>
</main>

<?php snippet('footer') ?>
