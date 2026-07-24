# Kirby Markdown — attribution & licenses

Kirby's Markdown parser (`Kirby\Text\Markdown`) is a from-scratch, dependency-free implementation of the **CommonMark** specification, extended with the Markdown features Kirby has long supported via Parsedown Extra, e.g. footnotes, definition lists, abbreviations, tables, `{#id .class}` attributes, strikethrough and bare-URL autolinking. It began as a reimplementation of **Parsedown** and **ParsedownExtra**, the libraries Kirby previously bundled, and some behaviour and specific data still derive from them; where they diverge from CommonMark, the parser follows the spec. It bundles no third-party code.

The parser itself is © Bastian Allgeier and released under the MIT license (see each source file's header). The work it builds on is credited below.

> The markdown **input fixtures** used by the test suite have their own,
> separate attribution in
> [`tests/Text/fixtures/markdown/ATTRIBUTION.md`](../../../tests/Text/fixtures/markdown/ATTRIBUTION.md).

---

## CommonMark specification

<https://spec.commonmark.org/>

The parser targets **CommonMark 0.31.2** and follows the two-phase parsing strategy the spec documents. It implements the specification only; no CommonMark reference-implementation code (`cmark`, `commonmark.js`) is used.

The CommonMark spec is authored by John MacFarlane et al. and released under CC BY-SA 4.0. Kirby implements the spec but does not redistribute its text here; the spec's `spec.txt`, reused verbatim as a test fixture, is attributed under that license in [`tests/Text/fixtures/markdown/ATTRIBUTION.md`](../../../tests/Text/fixtures/markdown/ATTRIBUTION.md).

---

## Parsedown — MIT

<https://github.com/erusev/parsedown>

```
The MIT License (MIT)

Copyright (c) 2013-2018 Emanuil Rusev, erusev.com

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```

---

## ParsedownExtra — MIT

<https://github.com/erusev/parsedown-extra>

```
The MIT License (MIT)

Copyright (c) 2013 Emanuil Rusev, erusev.com

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```
