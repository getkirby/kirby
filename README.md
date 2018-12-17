# Kirby

[![Build Status](https://travis-ci.com/k-next/kirby.svg?branch=master)](https://travis-ci.com/k-next/kirby)
[![Coverage Status](https://coveralls.io/repos/github/k-next/kirby/badge.svg?branch=master)](https://coveralls.io/github/k-next/kirby?branch=master)

This is Kirby's core application folder. If you are looking for a full working installation you should be heading over to the [Starterkit](https://github.com/k-next/starterkit) or to the [Plainkit](https://github.com/k-next/plainkit).

## Bug reports

Please post all bug reports in our issue tracker:   
https://github.com/k-next/kirby/issues

## Feature suggestions

If you want to suggest features or enhancements for Kirby, please use our Ideas repository:  
https://github.com/k-next/ideas/issues

## Installation

Once you've cloned the repository you need to install the composer dependencies and the panel npm modules.

```
composer install
cd panel
npm i
```

As a next step you should create your own `.env` file from the `.env.sample` in the panel folder. Set your PHP host in there to point the webpack proxy to the right place. 

## Commands

### Running the panel 

```
cd panel
npm run serve
```

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
