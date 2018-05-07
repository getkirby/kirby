<?php snippet('header') ?>

<main class="contact">
  <?php snippet('intro') ?>

  <section>
    <h2>Get in Touch</h2>
    <ul class="contact-options">
      <?php foreach ($page->contactoptions()->toStructure() as $item): ?>
        <li class="text">
          <h3><?= $item->title()->html() ?></h3>
          <p class="contact-item-text">
            <?= $item->text()->html() ?>
          </p>
          <p class="contact-item-action">
            <a href="<?= $item->url()->html() ?>"><?= $item->linktext()->html() ?></a>
          </p>
        </li>
      <?php endforeach ?>
    </ul>
  </section>

  <section class="twitter">
    <h2>Follow us on Twitter</h2>

    <ul>
      <?php foreach ($page->twitter()->toStructure() as $account): ?>
      <li>
        <a href="https://twitter.com/<?= $account->twitter() ?>">
          <p class="twitter-name"><?= $account->name() ?></p>
          <p class="twitter-account">@<?= $account->twitter() ?></p>
        </a>
      </li>
      <?php endforeach ?>
    </ul>
  </section>

</main>

<?php snippet('footer') ?>
