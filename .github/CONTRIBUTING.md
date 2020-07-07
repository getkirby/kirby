# Contributing

:+1::tada: First off, yes, you can contribute and thanks already for taking the time if you do! :tada::+1:

## Our branches setup

| Branch | Used for | PRs allowed? |
|--|--|--|
| `master` | Latest released version | - |
| `release/*` | Pre-releases in testing before they are merged into `master` when released | - |
| `develop` | Working branch for next patch or feature version, e.g. `3.0.x` or `3.x` | send PRs here, we will review and, if decided, merge once appropriate version is upcoming |
| `fix/*` | Temporary branches for single patch | - |
| `feature/*` | Temporary branches for single feature | - |

## Bug reports

Helping us understand bugs you encountered is the first step to support us in fixing them. When you create a bug report, please include as many details as possible. Fill out [the required template](ISSUE_TEMPLATE/bug_report.md), the requested information helps us resolve issues faster.

## Bug fixes

Create feature PRs on a new bugfix branch. Follow the branch name scheme: `fix/issue_number-bug-x`. Limit bug fix pull-requests (PRs) to a single bug. **Do not mix multiple bug fixes in a single PR.** This will make it easier for us to review the fix and merge it.

Always send bug fix PRs to the `develop` branch––not `master`! Add a helpful description of what the PR does if it is not 100% self-explanatory. Every bug fix should also be combined with a unit test to avoid future regressions. Let us know if you need help with that.

Make sure your branch is up to date with the latest state on the `develop` branch. [Rebase](https://help.github.com/articles/about-pull-request-merges/) changes before you send the PR.

Fix code style issues with [PHP CS](https://github.com/FriendsOfPHP/PHP-CS-Fixer) before you submit the PR. [Install PHP CS globally](https://github.com/FriendsOfPHP/PHP-CS-Fixer#globally-composer) via composer and then run `composer fix` in the kirby repository. Our tests will fail if there are CS issues in your code.

## Translations

We are really happy about any help with our translations. Please, do not translate directly in the JSON files though. We use a service called Transifex to handle all languages for the Panel: https://www.transifex.com/getkirby/panel/ Create an account there and send us a request to join our translator group. Please, also send us an email at <support@getkirby.com>. Unfortunately, we don't get notified properly about new translator requests and often miss them.

## Features

Create feature PRs on a new feature branch. Follow the branch name scheme: `feature/issue_number-feature-x`. Always send feature PRs to the `develop` branch––not `master`!

We try to bundle features in our major releases, e.g. `3.x`. That is why we might only review and, if decided, merge your PR once an appropriate  release for your PR is upcoming.

Have a look at the [ideas repository](https://github.com/getkirby/ideas/issues). Maybe your feature idea already exists and you can get valuable feedback from other Kirby users.

### Additional rules:

1. New features must have unit tests
2. Fix code style issues with CS fixer and `composer fix` before you submit the PR
3. Add a helpful description
4. Focus on a single feature per PR. Don't mix features!
5. Write human-readable commit messages. We might use them for the changelog.

Please understand that we cannot merge all feature ideas or that it might take a while. Check out the [roadmap](https://roadmap.getkirby.com) to see upcoming releases.

Let us know [in the forum](https://forum.getkirby.com) if you have questions.

**And once more: thank you!** :+1::tada:
