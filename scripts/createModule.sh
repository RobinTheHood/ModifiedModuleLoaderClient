#!/bin/bash

# USAGE
# Diese Datei aus dem Root Verzeichnis aufrufen
# ./scripts/createModule.sh vendorPrefix vendorName moduleName
#
# Beispiel
# ./scripts/createModule.sh mc mycompany my-first-module

vendorPrefix=$1
vendorName=$2
moduleName=$3

if [ -z "$vendorPrefix" ]
then
    echo "No vendorPrefix. Try again."
    exit 0
fi

if [ -z "$vendorName" ]
then
    echo "No vendorName. Try again."
    exit 0
fi

if [ -z "$moduleName" ]
then
    echo "No moduleName. Try again."
    exit 0
fi

php -r "
    require 'vendor/autoload.php';

    use RobinTheHood\ModifiedModuleLoaderClient\ModuleCreator;

    \$moduleCreator = new ModuleCreator();
    \$moduleCreator->createModule('$vendorPrefix', '$vendorName', '$moduleName');
"