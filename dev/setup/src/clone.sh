#!/bin/bash

# Clone each plugin repository
while read repo; do

   echo "### 1. Clone $repo"
   git clone --depth 1 git@github.com:moderntribe/$repo ./wp-content/plugins/$repo

done < ./dev/setup/plugins.txt
