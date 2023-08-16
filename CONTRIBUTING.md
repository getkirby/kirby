# Contributing

:+1::tada: First off, yes, you can contribute and thanks already for taking the time if you do! :tada::+1:

## How we organize code

To keep track of different states of our code (current release, bugfixes, features) we use branches:

| Branch      | Used for                                                                 | PRs allowed?                |
| ----------- | ------------------------------------------------------------------------ | --------------------------- |
| `main`      | Latest released version                                                  | -                           |
| `develop`   | Working branch for next release, e.g. `3.7.x`                            | ✅                          |
| `fix/*`     | Temporary branches for single patch                                      | -                           |
| `feature/*` | Temporary branches for single feature                                    | -                           |
| `release/*` | Pre-releases in testing before they are merged into `main` when released | only during release testing |

We will review all pull requests (PRs) to `develop` and merge them if accepted, once an appropriate version is upcoming. Please understand that this might not be the immediate next release and might take some time.

## How you can contribute

### Report a bug

When you find a bug, the first step to fixing it is to help us understand and reproduce the bug as best as possible. When you create a bug report, please include as many details as possible. Fill out [the template](https://github.com/getkirby/kirby/issues/new?template=bug_report.md) because the requested information helps us resolve issues so much faster.

### Bug fixes

For bug fixes, please create a new branch following the name scheme: `fix/issue_number-bug-x`, e.g. `fix/234-this-nasty-bug`. Limit bug fix PRs to a single bug. **Do not mix multiple bug fixes in a single PR.** This will make it easier for us to review the fix and merge it.

- Always send bug fix PRs against the `develop` branch––not `main`.
- Add a helpful description of what the PR does if it is not 100% self-explanatory.
- Every bug fix should include a [unit test](#tests) to avoid future regressions. Let us know if you need help with that.
- Make sure your code [style](#style) matches ours and includes [comments/in-code documentation](#documentation).
- Make sure your branch is up to date with the latest state on the `develop` branch. [Rebase](https://help.github.com/articles/about-pull-request-merges/) changes before you send the PR.
- Please *don't* commit updated dist files in the `panel/dist` folder to avoid merge conflicts. We only build the dist files on release. Your branch should only contain changes to the source files.

### Features

For features create a new branch following the name scheme: `feature/issue_number-feature-x`, e.g. `feature/123-awesome-function`. Our [feedback platform](https://feedback.getkirby.com) can be a good source of highly requested features. Maybe your feature idea already exists and you can get valuable feedback from other Kirby users. Focus on a single feature per PR. Don't mix features!

- Always send feature PRs against the `develop` branch––not `main`.
- Add a helpful description of what the PR does.
- New features should include [unit tests](#tests). Let us know if you need help with that.
- Make your code [style](#style) matches ours and includes [comments/in-code documentation](#documentation).
- Make sure your branch is up to date with the latest state on the `develop` branch. [Rebase](https://help.github.com/articles/about-pull-request-merges/) changes before you send the PR.
- Please *don't* commit updated dist files in the `panel/dist` folder to avoid merge conflicts. We only build the dist files on release. Your branch should only contain changes to the source files.

We try to bundle features in our major releases, e.g. `3.x`. That is why we might only review and, if accepted, merge your PR once an appropriate release is upcoming. Please understand that we cannot merge all feature ideas or that it might take a while. Check out the [roadmap](https://roadmap.getkirby.com) to see upcoming releases.

### Translations

We are really happy about any help with translations. Please do not directly translate JSON files, though. We use a service called Transifex to handle [all translations](https://translation.getkirby.com/). Create an account there and send us a request to join our translator group. Additionally, also send an email to <support@getkirby.com>. Unfortunately, we don't get notified properly about new translator requests.

## How we write code

### Style

#### Backend (PHP)

We use [PHP CS Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer) to ensure a consistent style for our PHP code. It is mainly based on [PSR-12](https://www.php-fig.org/psr/psr-12/). [Install PHP CS Fixer globally](https://github.com/FriendsOfPHP/PHP-CS-Fixer#globally-composer) via Composer and then run `composer fix` in the `kirby` folder to check for inconsistencies and fix them. Our automated PR checks will fail if there are code style issues with your code.

#### Frontend/Panel (JavaScript, Vue)

We use [Prettier](https://prettier.io) to ensure a consistent style for our JavaScript and Vue code. After running `npm install` in the `kirby/panel` folder, you can run `npm run format` to check for inconsistencies and fix them. We also use [ESLint](https://eslint.org) which you can use by running `npm run lint` and/or `npm run lint:fix`.

### Documentation

In-code documentation and comments help us understand each other's code - or our own code after some months. Especially when matters get more complicated, we try to add a lot of comments to explain what the code does or why we implemented it like this. Even better than good comments is good code that is easy to understand.

#### Backend (PHP)

We use PHP [DocBlocks](https://docs.phpdoc.org/guide/references/phpdoc/basic-syntax.html#what-is-a-docblock) for classes and methods.

#### Frontend/Panel (JavaScript, Vue)

We use [JSDoc](https://jsdoc.app) for documenting JavaScript code, especially for [Vue components](https://vue-styleguidist.github.io/docs/Documenting.html).

#### Public documentation

We also document Kirby on the Kirby website at <https://getkirby.com>. However we recommend to wait with writing public documentation until the feature PR is merged. If you don't know where the documentation for a feature best belongs, don't worry. We can take care of writing the docs.

### Tests

Unit and integration tests help us prevent regressions when we make changes to the code. Every bug fix should also add a unit test for the fixed bug to make sure we won't re-introduce the same problem later down the road. Every new feature should be accompanied by unit tests to protect it from breaking through future changes.

#### Backend (PHP)

We use [PHPUnit](https://phpunit.de) for unit test for our PHP code. You can find all existing tests in the [`kirby/tests` subfolders](https://github.com/getkirby/kirby/tree/main/tests). Take a look to see how we usually structure our tests.

#### Frontend/Panel (JavaScript, Vue)

The Panel doesn't have extensive test coverage yet. That's an area we are still trying to improve.

We use [vitest](https://vitest.dev) for unit tests for JavaScript and Vue components - `.test.js` files next to the actual JavaScript/Vue file.

## And last…

Let us know [in the forum](https://forum.getkirby.com) if you have questions.

**And once more: thank you!** :+1::tada:
