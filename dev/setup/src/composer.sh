#!/bin/bash

# Run composer for each plugin
while read repo; do

   echo "### 2. Composer $repo"
   (cd ./wp-content/plugins/$repo && composer install --ignore-platform-reqs)

done < ./dev/setup/plugins.txt
