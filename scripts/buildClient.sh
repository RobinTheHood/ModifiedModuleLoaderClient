#!/bin/bash

# *** USAGE ***
# Execute this script from the mmlc root directory
# ./scripts/buildClient.sh Git-Tag-Version


TAG_VERSION=$1

# Prepare build directory
mkdir ./build
rm -rf ./build/ModifiedModuleLoaderClient.tar

# Git
echo "Build Version: ${TAG_VERSION}"
git clone git@github.com:RobinTheHood/ModifiedModuleLoaderClient.git ./build/ModifiedModuleLoaderClient -b ${TAG_VERSION}
rm -rf ./build/ModifiedModuleLoaderClient/.git

# Detect fileVersion
# Add Version
FILE_VERSION=$(php -r "echo json_decode(file_get_contents('build/ModifiedModuleLoaderClient/config/version.json'))->version;")
echo "Detect FileVersion: ${FILE_VERSION}"

if [ "${FILE_VERSION}" != "${TAG_VERSION}" ]; then
    rm -rf ./build/ModifiedModuleLoaderClient
    echo "Error: versions are not equal. Can't build version."
    exit 1
fi

# Install dependencies
composer install -d ./build/ModifiedModuleLoaderClient --no-dev
composer update -d ./build/ModifiedModuleLoaderClient --no-dev

# Rename config.php
mv ./build/ModifiedModuleLoaderClient/config/_config.php ./build/ModifiedModuleLoaderClient/config/config.php

# Delete directories even if they do not exist
rm -rf ./build/ModifiedModuleLoaderClientLocal/.github
rm -rf ./build/ModifiedModuleLoaderClientLocal/.vscode
rm -rf ./build/ModifiedModuleLoaderClientLocal/Archives
rm -rf ./build/ModifiedModuleLoaderClientLocal/build
# keep ./build/ModifiedModuleLoaderClientLocal/config
rm -rf ./build/ModifiedModuleLoaderClientLocal/docs
rm -rf ./build/ModifiedModuleLoaderClientLocal/logs
rm -rf ./build/ModifiedModuleLoaderClientLocal/Modules
rm -rf ./build/ModifiedModuleLoaderClientLocal/private
# keep ./build/ModifiedModuleLoaderClientLocal/scripts
# keep ./build/ModifiedModuleLoaderClientLocal/src
rm -rf ./build/ModifiedModuleLoaderClientLocal/tests
# keep ./build/ModifiedModuleLoaderClientLocal/vendor

# Delete files from root
rm ./build/ModifiedModuleLoaderClientLocal/.gitignore
rm ./build/ModifiedModuleLoaderClientLocal/codeception.yml
rm ./build/ModifiedModuleLoaderClientLocal/composer.json
rm ./build/ModifiedModuleLoaderClientLocal/composer.lock
rm ./build/ModifiedModuleLoaderClientLocal/icon.png
# keep ./build/ModifiedModuleLoaderClientLocal/index.php
rm ./build/ModifiedModuleLoaderClientLocal/mmlc_installer.php
rm ./build/ModifiedModuleLoaderClientLocal/phpunit.xml
rm ./build/ModifiedModuleLoaderClientLocal/psalm-baseline.xml
rm ./build/ModifiedModuleLoaderClientLocal/psalm.xml
rm ./build/ModifiedModuleLoaderClientLocal/README.md

# Delete files from scripts/
rm ./build/ModifiedModuleLoaderClientLocal/scripts/buildClient.sh
rm ./build/ModifiedModuleLoaderClientLocal/scripts/buildClientFromLocal.sh
# keep ./build/ModifiedModuleLoaderClientLocal/scripts/createModule.sh

# Create empty directories
mkdir ./build/ModifiedModuleLoaderClient/Modules
mkdir ./build/ModifiedModuleLoaderClient/Archives

# Create tar file
COPYFILE_DISABLE=1 tar -C ./build/ -cf ./build/ModifiedModuleLoaderClient.tar ModifiedModuleLoaderClient/

# Add Version
cp ./build/ModifiedModuleLoaderClient.tar ./build/ModifiedModuleLoaderClient_v${FILE_VERSION}.tar

rm -rf ./build/ModifiedModuleLoaderClient