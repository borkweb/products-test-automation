#!/usr/bin/env bash

#############################################################
# Tric Global Installer
#############################################################

SCRIPTDIR=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd );
PHAR_NAME="tric.phar"

# Functions
install_phar() {
  PHAR_DOWNLOAD=$(curl -s https://api.github.com/repos/moderntribe/products-test-automation/releases/latest \
        | grep browser_download_url \
        | grep ${PHAR_NAME} \
        | cut -d '"' -f 4)

  curl -s -L --create-dirs "${PHAR_DOWNLOAD}" -o ${CONFIG_DIR}/bin/tric
  chmod +x ${CONFIG_DIR}/bin/tric
  sudo ln -s ${CONFIG_DIR}/bin/tric /usr/local/bin/tric
}

# OSX
if [[ "$OSTYPE" == "darwin"* ]]; then
  command -v docker >/dev/null 2>&1
  if [[ $? != 0 ]] ; then
      echo "Docker appears to be missing, install it from here: https://hub.docker.com/editions/community/docker-ce-desktop-mac"
      exit 1;
  fi
fi

echo "Downloading tric.phar to /usr/local/bin/tric, enter your password when requested."
install_phar

tric

echo ""
echo "************************************"
echo "If everything went smoothly, you should see the 'tric' command list above."
echo "************************************"
