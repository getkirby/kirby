# Kirby Panel

## Installation

The Panel dev server is a proxy of another web server like Apache or Nginx that serves an actual Kirby project. Point the dev server to that project with an `.env` file similar to the `.env.example` one in this folder:

```
VUE_APP_DEV_SERVER = http://kir.by
```

…where `kir.by` is the project domain. You can set up such a domain by editing your machine's `hosts` file and your web server's configuration.

Make sure to add a dummy CSRF token in the `site/config/config.php` of your Kirby project:

```php
return [
  'api.csrf' => 'dev'
];
```

Afterwards install the Panel dependencies…

```
npm i
```

And start webpack:

```
npm run serve
```

## Commands

### Serve

To start the webpack watcher and browsersync

```
npm run serve
```

### Build

To upate the dist files

```
npm run build
```

### e2e

To start end to end tests via Cypress

```
npm run e2e
```
