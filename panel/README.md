# Kirby Panel

## Installation

Our setup expects that you are running [Kirby Sandbox](https://github.com/getkirby/sandbox), our our local test environment, at `https://sandbox.test`. You can reach the Panel at `https://sandbox.test/panel`.

We are using [Herd](https://herd.laravel.com) to run our setup locally.

If you are using a different setup, point `vite` to your server with a `SERVER` entry in `/panel/.env` (see `/panel/.env.example`). Further `vite` server options can go into a `/panel/vite.config.custom.js`.

### HTTPS

If HTTPS for your site is set up with [Herd](https://herd.laravel.com) or [Valet](https://laravel.com/docs/valet), the dev server uses its certificate automatically.

For any other setup, add the certificate to `/panel/vite.config.custom.js`:

```js
import fs from "fs";

export default {
	https: {
		key: fs.readFileSync("/path/to/sandbox.test.key"),
		cert: fs.readFileSync("/path/to/sandbox.test.crt")
	}
};
```

### `panel.dev` mode

When developing, make sure to put Kirby into development mode by adding the following line to `site/config/config.php` of your Kirby project (unless you are using the Sandbox, which uses dev mode by default):

```php
return [
  'panel.dev' => true
];
```

Afterwards install the Panel dependencies…

```
npm i
```

And start `vite`:

```
npm run dev
```

## Commands

### Serve

To start the `vite` development watcher and server

```
npm run dev
```

### Build

To upate the dist files

```
npm run build
```

## Browser support

`.browserslistrc` is the single source of truth. It feeds the Vite build target (`createTarget()` in `vite.config.ts`) and the "browser too old" page ( `views/browser.php`). Don't repeat the versions anywhere else.

To move the floor for the next major, see the instructions in `.browserslistrc`.
