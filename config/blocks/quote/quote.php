<?php if ($text->isNotEmpty()): ?>
<blockquote>
  <?= $text ?>
  <?php if ($citation->isNotEmpty()): ?>
  <footer>
    <?= $citation ?>
  </footer>
  <?php endif ?>
</blockquote>
<?php endif ?>
