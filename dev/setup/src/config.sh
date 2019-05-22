#!/bin/bash

# Run submodules for each plugin
while read repo; do

   echo "### 3. Config $repo"
   (cp dev/test/config/codeception.yml ./wp-content/plugins/$repo/codeception.yml)
   (cp dev/test/config/wpunit.suite.yml ./wp-content/plugins/$repo/tests/wpunit.suite.yml)
   (cp dev/test/config/.env.local.tpl ./wp-content/plugins/$repo/.env.local)

done < ./dev/setup/plugins.txt
