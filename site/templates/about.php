<?php snippet('header') ?>

  <main class="main" role="main">

    <div class="wrap">
      
      <header>
        <h1><?= $page->title()->html() ?></h1>
        <div class="intro text">
          <?= $page->intro()->kirbytext() ?>
        </div>
        <hr />
      </header>
      
      <div class="text">
        <?= $page->text()->kirbytext() ?>
      </div>
      
    </div>
    
    <section class="team wrap wide">
      
      <h2>Our Purring Team</h2>

      <ul class="team-list grid gutter-1">
        <?php foreach($page->children()->visible() as $member): ?>
          <li class="team-item column">
            
            <figure class="team-portrait">
              <img src="<?= $member->image()->url() ?>" alt="Portrait of <?= $member->title()->html() ?>" />
            </figure>
            
            <div class="team-info">
              <h3 class="team-name"><?= $member->title()->html() ?></h3>
              <p class="team-position"><?= $member->position()->html() ?></p>
              <div class="team-about text">
                <?= $member->about()->kirbytext() ?>
              </div>
            </div>
            
            <div class="team-contact text">
              <i>Phone:</i><br />
              <?= kirbytag(['tel' => $member->phone()->html()]) ?><br />
              <i>Email:</i><br />
              <a href="mailto:<?= $member->email()->html() ?>"><?= $member->email()->html() ?></a><br />
            </div>
          </li>
        <?php endforeach ?>
      </ul>
      
    </section>

  </main>

<?php snippet('footer') ?>