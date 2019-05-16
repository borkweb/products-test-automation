#!/bin/bash

# This File will Build the local environment for a local dev setup. This file can be compared to the JenkinsFile in the root, as it uses the same src scripts to build the environment for CI purposes.

cd .. && cd ..

# Install events-test dependencies
composer install

# Clone plugin repositories
bash ./dev/setup/src/clone.sh

# Composer install plugins
bash ./dev/setup/src/composer.sh

# Submodules plugins
bash ./dev/setup/src/submodules.sh

# NPM Installs
bash ./dev/setup/src/npm.sh

# Gulp Installs
bash ./dev/setup/src/gulp.sh

# CP configs
bash ./dev/setup/src/config.sh

# Tests, run on your own ;)
# bash ./dev/setup/src/tests.sh
