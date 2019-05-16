#!/bin/bash

# Run wpunit tests in each repository
while read repo; do

   echo "### 1. Test $repo"
   (cd ./wp-content/plugins/$repo && ./vendor/bin/codecept run wpunit)

done < ./dev/setup/plugins.txt
