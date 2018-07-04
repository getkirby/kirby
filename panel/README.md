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

## How to build

```
npm run build
```

## e2e Tests

We are using cypress for end to end tests. You can start it with…

```
npm run e2e
```
