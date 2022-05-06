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
rm -rf ./build/ModifiedModuleLoaderClientLocal/private
rm -rf ./build/ModifiedModuleLoaderClientLocal/docs
rm -rf ./build/ModifiedModuleLoaderClientLocal/tests
rm -rf ./build/ModifiedModuleLoaderClientLocal/patches
rm -rf ./build/ModifiedModuleLoaderClientLocal/Modules
rm -rf ./build/ModifiedModuleLoaderClientLocal/Archives
rm -rf ./build/ModifiedModuleLoaderClientLocal/.github

# Delete files
rm ./build/ModifiedModuleLoaderClientLocal/README.md
rm ./build/ModifiedModuleLoaderClientLocal/composer.json
rm ./build/ModifiedModuleLoaderClientLocal/composer.lock
rm ./build/ModifiedModuleLoaderClientLocal/phpunit.xml
rm ./build/ModifiedModuleLoaderClientLocal/psalm.xml
rm ./build/ModifiedModuleLoaderClientLocal/psalm-baseline.xml
rm ./build/ModifiedModuleLoaderClientLocal/mmlc_installer.php
rm ./build/ModifiedModuleLoaderClientLocal/icon.png
rm ./build/ModifiedModuleLoaderClientLocal/scripts/buildClient.sh
rm ./build/ModifiedModuleLoaderClientLocal/scripts/buildClientFromLocal.sh
rm ./build/ModifiedModuleLoaderClientLocal/.gitignore

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
