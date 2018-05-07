<?php snippet('header') ?>

<main>
  <?php snippet('intro') ?>

  <ul class="team">
    <?php foreach ($page->children()->listed() as $member): ?>
    <li>
      <figure class="member">
        <span><?= $member->image()->crop(500) ?></span>
        <figcaption class="text">
          <h2 class="member-name"><?= $member->title() ?></h2>
          <p class="member-position"><?= $member->position() ?></p>
          <p class="member-text"><?= $member->about()->kt() ?></p>
          <p class="member-email"><a href="mailto:<?= $member->email() ?>"><?= $member->email() ?></a></p>
        </figcaption>
      </figure>
    </li>
    <?php endforeach ?>
  </ul>
</main>

<?php snippet('footer') ?>
