<article class="article">
  <header class="article-header">
    <a href="<?= $article->url() ?>">
      <?php if ($article->is($page) === false): ?>
      <h2><?= $article->title() ?></h2>
      <?php endif ?>
      <time><?= $article->date('d F Y') ?></time>

      <p class="article-tags"> # <?= $article->tags() ?></p>

      <?php if ($author = $article->author()->toUser()): ?>
      <p class="article-author">by <?= $author->name() ?></p>
      <?php endif ?>
    </a>
  </header>

  <div class="article-text text">
    <?= $article->text()->kt() ?>
  </div>
</article>
