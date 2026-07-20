<?php /** @var \Kirby\Cms\Block $block */ ?>
<pre><code class="language-<?= $block->language()->or('text')->escape('attr') ?>"><?= $block->code()->html(false) ?></code></pre>
