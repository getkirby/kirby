<?php snippet('header') ?>

<main>
  <header class="intro">
    <h1>Kirby Kitchensink</h1>
    <div class="intro-text">
      There's magic in every new beginning
    </div>
  </header>

  <ul class="grid">
    <?php foreach ($site->children()->listed() as $example): ?>
    <li>
      <a href="<?= $example->url() ?>">
        <figure>
          <img src="https://picsum.photos/500/500/?random&t=<?= $example->indexOf() ?>" alt="">
          <figcaption>
            <span>
              <span class="example-icon"><?= $example->blueprint()->icon() ?></span>
              <span class="example-name"><?= $example->title() ?></span>
            </span>
          </figcaption>
        </figure>
      </a>
    </li>
    <?php endforeach ?>
  </ul>

</main>

<?php snippet('footer') ?>
