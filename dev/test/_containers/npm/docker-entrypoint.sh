#!/usr/bin/env bash

# This file is just a proxy to call the `npm` binary that will, but, take care of fixing file mode issues before.

eval $( fixuid )

npm --prefix /project "$@"
