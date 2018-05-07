<?php snippet('header') ?>
<main>
  <?php snippet('intro') ?>

  <ul class="testimonials">
    <?php foreach ($page->children()->listed() as $testimonial): ?>
    <li class="testimonial">
      <figure class="member">
        <span><?= $testimonial->image()->crop(128) ?></span>
      </figure>
      <blockquote>
        <p class="testimonial-quote"><?= $testimonial->text() ?></p>
        <p class="testimonial-author"><a href="">@<?= $testimonial->username() ?></a></p>
      </blockquote>
    </li>
    <?php endforeach ?>
  </ul>
</main>

<?php snippet('footer') ?>
