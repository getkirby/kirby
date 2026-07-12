# Markdown test fixtures

Fixtures for `Kirby\Text\Markdown` (`MarkdownTest`). Two layers:

```
commonmark-spec.txt   CommonMark 0.31.2 spec.txt (652 examples, verbatim)
features/  *.md *.html Kirby features CommonMark doesn't cover  (breaks=true, safe=false)
safe/      *.md *.html safe-mode escaping / URL neutralisation  (breaks=true, safe=true)
inline/    *.md *.html inline parsing, parse($text, inline: true)
ATTRIBUTION.md         source + license of commonmark-spec.txt
```

## CommonMark conformance

`Kirby\Text\MarkdownTest::testCommonMark` parses every `example` block of `commonmark-spec.txt` and asserts Kirby's output against the spec's own expected HTML, on the `breaks=false` profile the canonical HTML is defined for. It is both a conformance check and a regression guard.

`Kirby\Text\MarkdownTest::MarkdownTest::DIVERGENCES` keeps track of examples where our library does not produce the expected outcome (intentionally or as gap to the spec). To refresh the spec, drop a newer `spec.txt` in and re-run; new mismatches surface as failures.

## Kirby feature fixtures

`Kirby\Text\MarkdownTest::testFixture` renders each `<dir>/<name>.md` under its directory's profile and compares to the committed `<name>.html`. These pin Kirby-specific behaviour (footnotes, definition lists, abbreviations, `{#id .class}` attributes, tables, strikethrough, bare-URL autolinking, safe mode, inline parsing). The `.html` files are Kirby's own current output.

Kirby's runtime default is `breaks=true, safe=false`; `safe` and `inline` fixtures pin the two option axes that actually change output.

## Benchmarking

The spec/feature fixtures are a correctness oracle. For a meaningful performance check, bench against real content, pull real Markdown from the public Kirby content repos (point the bench at its `content/` directory), e.g. starterkit, demokit or getkirby.com content.

`bench.php` uses a tiny PSR-4 loader pointed at a checkout's `src/`, so the same corpus can be timed across branches without composer:

```php
<?php
// php bench.php /path/to/kirby/src /path/to/site/content [iterations]
$src   = $argv[1];
$dir   = $argv[2];
$iters = (int)($argv[3] ?? 20);

spl_autoload_register(function ($class) use ($src) {
    if (str_starts_with($class, 'Kirby\\')) {
        $file = $src . '/' . str_replace('\\', '/', substr($class, 6)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

use Kirby\Text\Markdown;

$docs = [];
$it   = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    if ($file->getExtension() === 'txt') {
        $docs[] = file_get_contents($file->getPathname());
    }
}

$md = new Markdown(['breaks' => false, 'safe' => false]);
foreach ($docs as $d) $md->parse($d);   // warm up

$best = INF;
for ($r = 0; $r < 5; $r++) {             // best-of-5 resists transient load
    $t = hrtime(true);
    for ($i = 0; $i < $iters; $i++) {
        foreach ($docs as $d) $md->parse($d);
    }
    $best = min($best, (hrtime(true) - $t) / 1e9);
}
printf("%d docs, best-of-5: %.1f us/doc\n", count($docs), $best / $iters / count($docs) * 1e6);
```
