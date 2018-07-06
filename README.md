# Kirby

[![Build Status](https://travis-ci.com/k-next/kirby.svg?branch=master)](https://travis-ci.com/k-next/kirby)
[![Coverage Status](https://coveralls.io/repos/github/k-next/kirby/badge.svg?branch=master)](https://coveralls.io/github/k-next/kirby?branch=master)

## Installation

```
composer install
```

## Commands

### Archiving and zipping

To zip the current version and remove all unnecessary files, run the following script:

```
composer run-script zip
```

### Build

To create a current build, run the following script:

```
composer run-script build
```

This will run the following steps:

#### Panel
1. `npm i`
2. `npm run build`

#### Kirby
1. `composer run-script zip`

A `dist.zip` will be saved afterwards in the kirby directory and can be distributed and placed in all kits. This file is ignored by default and should never be added to the repository.
