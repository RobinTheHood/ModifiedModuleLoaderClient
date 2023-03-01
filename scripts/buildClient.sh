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
# composer update -d ./build/ModifiedModuleLoaderClient --no-dev

# Rename config.php
mv ./build/ModifiedModuleLoaderClient/config/_config.php ./build/ModifiedModuleLoaderClient/config/config.php

# Delete directories even if they do not exist
rm -rf ./build/ModifiedModuleLoaderClient/.github
rm -rf ./build/ModifiedModuleLoaderClient/.vscode
rm -rf ./build/ModifiedModuleLoaderClient/Archives
rm -rf ./build/ModifiedModuleLoaderClient/build
# keep ./build/ModifiedModuleLoaderClient/config
rm -rf ./build/ModifiedModuleLoaderClient/docs
rm -rf ./build/ModifiedModuleLoaderClient/logs
rm -rf ./build/ModifiedModuleLoaderClient/Modules
rm -rf ./build/ModifiedModuleLoaderClient/private
# keep ./build/ModifiedModuleLoaderClient/scripts
# keep ./build/ModifiedModuleLoaderClient/src
rm -rf ./build/ModifiedModuleLoaderClient/tests
# keep ./build/ModifiedModuleLoaderClient/vendor

# Delete files from root
rm ./build/ModifiedModuleLoaderClient/.gitignore
rm ./build/ModifiedModuleLoaderClient/codeception.yml
rm ./build/ModifiedModuleLoaderClient/composer.json
rm ./build/ModifiedModuleLoaderClient/composer.lock
rm ./build/ModifiedModuleLoaderClient/icon.png
# keep ./build/ModifiedModuleLoaderClient/index.php
rm ./build/ModifiedModuleLoaderClient/mmlc_installer.php
rm ./build/ModifiedModuleLoaderClient/phpunit.xml
rm ./build/ModifiedModuleLoaderClient/psalm-baseline.xml
rm ./build/ModifiedModuleLoaderClient/psalm.xml
rm ./build/ModifiedModuleLoaderClient/README.md

# Delete files from scripts/
rm ./build/ModifiedModuleLoaderClient/scripts/buildClient.sh
rm ./build/ModifiedModuleLoaderClient/scripts/buildClientFromLocal.sh
# keep ./build/ModifiedModuleLoaderClient/scripts/createModule.sh

# Create empty directories
mkdir ./build/ModifiedModuleLoaderClient/Modules
mkdir ./build/ModifiedModuleLoaderClient/Archives

# Create tar file
COPYFILE_DISABLE=1 tar -C ./build/ -cf ./build/ModifiedModuleLoaderClient.tar ModifiedModuleLoaderClient/

# Add Version
cp ./build/ModifiedModuleLoaderClient.tar ./build/ModifiedModuleLoaderClient_v${FILE_VERSION}.tar

rm -rf ./build/ModifiedModuleLoaderClient