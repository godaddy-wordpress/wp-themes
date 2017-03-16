# GoDaddy WordPress Themes #

Manifest of all WordPress Themes created by GoDaddy.

### How to update

1. Clone this repo
2. Run `cp pre-commit .git/hooks/pre-commit`
3. Run `git checkout develop` and **ONLY** make changes to `manifest.json`
4. Commit your changes (`grunt` and unit tests will run automatically)
5. If the tests pass, do a `git push`
6. Issue a PR against `master` (requires peer approval to merge)
