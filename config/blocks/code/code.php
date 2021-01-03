<?php /** @var \Kirby\Cms\Block $block */ ?>
<pre><code class="language-<?= $block->language()->or('text') ?>"><?= $block->code()->html(false) ?></code></pre>
