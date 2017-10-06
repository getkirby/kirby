<?php snippet('header') ?>

  <main class="main" role="main">
    
    <header class="wrap">
      <h1><?= $page->title()->html() ?></h1>      
      <div class="intro text">
        <?= $page->intro()->kirbytext() ?>
      </div>    
      <hr />      
    </header>
    
    <div class="wrap wide">
      <h2>Get in Touch</h2>
      
      <ul class="contact-options">
        <?php foreach($page->contactoptions()->toStructure() as $item): ?>
          <?php $icon = $page->image($item->icon()); ?>
          <li class="contact-item column">
            <div class="contact-item-content">
              <img src="<?= $icon->url() ?>" width="<?= $icon->width() ?>" alt="<?= $item->title()->html() ?> icon" class="contact-item-icon" />
              <h3 class="contact-item-title"><?= $item->title()->html() ?></h3>
              <p class="contact-item-text">
                <?= $item->text()->html() ?>
              </p>
            </div>
            <p class="contact-item-action">
              <a href="<?= $item->url()->html() ?>" class="contact-action btn"><?= $item->linktext()->html() ?></a>
            </p>
          </li>
        <?php endforeach ?>
      </ul>
    </div>
      
    <div class="contact-twitter text wrap cf">
      <?= $page->text()->kirbytext() ?>
    </div>
    
  </main>

<?php snippet('footer') ?>