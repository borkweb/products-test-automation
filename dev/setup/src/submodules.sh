#!/bin/bash

# Run submodules for each plugin
while read repo; do

   echo "### 3. Submodules $repo"
   (cd ./wp-content/plugins/$repo && git submodule update --init --recursive)

done < ./dev/setup/plugins.txt
