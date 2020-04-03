#!/usr/bin/env bash

# This script will use the docker container defined in the dev/test/_containers/generate-shell-functions directory to
# generate the interactive shell bootstrap file that ports the functions defined in the `Tribe\Test` namespace to the
# global namespace to make them usable in the shell.

# Find where this script is running from.
SCRIPT_DIR="$( cd "$(dirname "$0")" >/dev/null 2>&1 || exit ; pwd -P )"
ROOT_DIR="$(dirname "${SCRIPT_DIR}")"

IMAGE="products-test-automation-generate-shell-functions"

# Build the image if required.
test -z "$(docker images "${IMAGE}" -q)" \
  && {
    echo "Image ${IMAGE} not found, building it.";
    docker build "${SCRIPT_DIR}/test/_containers/generate-shell-functions" --tag "${IMAGE}";
  }

# Generate the stubs.
echo "Generating the dev/setup/src/php-shell-functions.php file..."
docker run --rm -v "${ROOT_DIR}":/project "${IMAGE}"

echo -e "\033[32mShell functions generated in the dev/setup/src/php-shell-functions.php file.\033[0m"
