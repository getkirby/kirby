# Kirby Panel

## Installation

Our setup expects that you are running [Kirby Sandbox](https://github.com/getkirby/sandbox), our our local test environment, at `http://sandbox.test`. You can reach the Panel at `http://sandbox.test/panel`.

If you are using a different setup, you might need to create a `/panel/vite.config.custom.js` where you can point `vite` to the right server.

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

### Test

To start end to end tests via Cypress

```
npm run test
```
