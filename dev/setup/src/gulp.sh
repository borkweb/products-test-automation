#!/bin/bash

# Run Gulp for each plugin
while read repo; do

   echo "### 5. Gulp $repo"
   (cd ./wp-content/plugins/$repo && gulp)

done < ./dev/setup/plugins.txt
