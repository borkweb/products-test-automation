# Setup

## Required Tools

There are a few tools that need to be installed for this stack to work.

1. Docker (recommended edge)
2. Node (latest stable) + Gulp
3. Composer (latest stable)
4. Git

## Automated Install

cd to `dev/setup`
run `bash setup-dev.sh`

This will install the test environment, plugin repos, and  build them.

## Manual Install (optional)

### Install dependencies

1. Run `composer install`

### Checkout each plugin

1. `git clone` each plugin to the `wp-content/plugins` folder. You can find the full list in `/dev/setup/plugins.txt`

* Why not Gitsubmodules? Cause they break easily.
* Why not composer then use `----prefer-source`? Because it's silly to have to re-run those commands for each repo.

### Build each repo

Each submodule has their own build process, so check each readme and build each as required.

* TODO: script a single build step for all plugins
