#!/bin/bash

# Run NPM for each plugin
while read repo; do

   echo "### 4. NPM $repo"
   (cd ./wp-content/plugins/$repo && npm install)

done < ./dev/setup/plugins.txt
