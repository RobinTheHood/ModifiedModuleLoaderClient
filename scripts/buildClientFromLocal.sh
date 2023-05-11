#!/bin/bash

# *** USAGE ***
# Execute this script from the mmlc root directory
# ./scripts/buildClientFromLocal.sh Semver-Version

NEW_VERSION=$1

# Prepare build directory
mkdir ./build
rm -rf ./build/ModifiedModuleLoaderClientLocal.tar
rm -rf ./build/ModifiedModuleLoaderClient
rm -rf ./build/ModifiedModuleLoaderClientLocal

echo "Build Version: ${NEW_VERSION}"

# Copy local files
mkdir ./build/ModifiedModuleLoaderClientLocal
cp -r ./config ./build/ModifiedModuleLoaderClientLocal/config
cp -r ./docs ./build/ModifiedModuleLoaderClientLocal/docs
cp -r ./scripts ./build/ModifiedModuleLoaderClientLocal/scripts
cp -r ./src ./build/ModifiedModuleLoaderClientLocal/src
cp -r ./vendor ./build/ModifiedModuleLoaderClientLocal/vendor
cp ./index.php ./build/ModifiedModuleLoaderClientLocal/index.php

# Setup config fildes
rm ./build/ModifiedModuleLoaderClientLocal/config/config.php
rm ./build/ModifiedModuleLoaderClientLocal/config/postUpdate
rm ./build/ModifiedModuleLoaderClientLocal/config/version.json
mv ./build/ModifiedModuleLoaderClientLocal/config/_config.php ./build/ModifiedModuleLoaderClientLocal/config/config.php
echo "{\"version\": \"${NEW_VERSION}\"}" > ./build/ModifiedModuleLoaderClientLocal/config/version.json

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
mkdir ./build/ModifiedModuleLoaderClientLocal/Modules
mkdir ./build/ModifiedModuleLoaderClientLocal/Archives

# Create tar file
mv ./build/ModifiedModuleLoaderClientLocal ./build/ModifiedModuleLoaderClient
COPYFILE_DISABLE=1 tar -C ./build/ -cf ./build/ModifiedModuleLoaderClientLocal.tar ModifiedModuleLoaderClient/

# Add Version
rm -f ./build/ModifiedModuleLoaderClientLocal_v${NEW_VERSION}.tar
cp ./build/ModifiedModuleLoaderClientLocal.tar ./build/ModifiedModuleLoaderClientLocal_v${NEW_VERSION}.tar

rm -rf ./build/ModifiedModuleLoaderClient
rm -rf ./build/ModifiedModuleLoaderClientLocal
