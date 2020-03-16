#!/usr/bin/env bash

BRANCH="$(printf "%q" "$1")"

# Silent, but show errors.
TIMESTAMP=$(curl -sSG \
  --data-urlencode "timestamp=1" \
  --data-urlencode "branch=${BRANCH}" \
  --data-urlencode "key=${NIGHTLY_SECRET}" \
  https://utility.tri.be/nightly.php | jq '.timestamp')

echo "${TIMESTAMP}"
