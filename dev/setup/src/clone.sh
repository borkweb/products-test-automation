#!/bin/bash

# Clone each plugin repository
while read repo; do

   echo "### 1. Clone $repo"
    if [ -d "./wp-content/plugins/$repo" ]; then
       (cd ./wp-content/plugins/$repo/ && git pull)
    else
        git clone --depth 1 git@github.com:moderntribe/$repo ./wp-content/plugins/$repo
    fi

done < ./dev/setup/plugins.txt
