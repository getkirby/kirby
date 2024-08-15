# Kirby Panel

## Installation

Our setup expects that you are running [Kirby Sandbox](https://github.com/getkirby/sandbox), our our local test environment, at `https://sandbox.test`. You can reach the Panel at `https://sandbox.test/panel`.

We are using [Herd](https://herd.laravel.com) to run our setup locally.

If you are using a different setup, you might need to create a `/panel/vite.config.custom.js` where you can point `vite` to the right server.

### Using Herd with HTTPS

If you're using [Herd](https://herd.laravel.com) in your local setup, you will need to enable HTTPS for the site and add the following `/panel/vite.config.custom.js`:

```node
/* eslint-env node */
import fs from "fs";

module.exports = {
	https: {
		key: fs.readFileSync(
			"/Users/XYZ/Library/Application Support/Herd/config/valet/Certificates/sandbox.test.key"
		),
		cert: fs.readFileSync(
			"/Users/XYZ/Library/Application Support/Herd/config/valet/Certificates/sandbox.test.crt"
		)
	}
};
```

Replace `XYZ` with your username. Adapt the whole path if those Herd files are located elsewhere (e.g. on Windows).

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
