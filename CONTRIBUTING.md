# Contributing

## Bug fixes

Please keep bug fix PRs focused on a single bug. Don't mix multiple bug fixes in a single PR. This will make it easier for us to review the fix and merge it. 

Always send bug fix PRs to the develop branch––not master! Add a helpful description of what the PR does if it is not 100% self-explanatory. Every bug fix should also be combined with a unit test to avoid future regressions. Let us know if you need help with that. 

Make sure that your branch is up to date with the latest state on the develop branch. Rebase changes before you send the PR. 

Fix code style issues with [PHP CS](https://github.com/FriendsOfPHP/PHP-CS-Fixer) before you submit the PR. Install PHP CS globally via composer and then run `composer fix` in the kirby repo. Our tests will fail if there are CS issues in your code.

## Features

Create feature PRs on a new feature branch. Follow the branch name scheme: `feature/yourname-feature-x`. 

Please have a look at the ideas repository https://github.com/getkirby/ideas/issues. Maybe your feature idea already exists and you can get valuable feedback from other Kirby users. 

### Additional rules: 

1. New features must have unit tests
2. Fix code style issues before you submit the PR with CS fixer and `composer fix`
3. Add a helpful description
4. Focus on a single feature per PR. Don't mix features!
5. Make sure you use a feature branch and you send the PR to develop––not master!
6. Write human-readable commit messages. We might use them for the changelog. 

Please understand that we cannot merge all feature ideas or that it might take a while. Features will also have to wait at least until the next feature release. Check out the roadmap to see upcoming releases: https://roadmap.getkirby.com

Let us know in the forum if you have questions: https://forum.getkirby.com
