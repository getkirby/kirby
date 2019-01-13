# Kirby Panel

## Installation

Create a new `.env` file in the panel folder and add the following line:

```
VUE_APP_DEV_SERVER = http://kir.by
```

Instead of http://kir.by point to the base URL where the Kirby installation is running on Apache or Nginx, etc.

Afterwards install the panel dependencies…

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
