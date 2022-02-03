#!/bin/bash 

mkdir -p dist

# Remove old packages
rm -rf ./dist/gravity-forms-encryption ./dist/gravity-forms-encryption.zip

# Copy current dir to tmp
rsync -ua . ./dist/gravity-forms-encryption/

# Remove current vendor folder (if any) and install 
# the dependencies without dev packages.
cd ./dist/gravity-forms-encryption || exit
rm -rf ./vendor/
composer install -o --no-dev

# Remove unneeded files in a WordPress plugin
rm -rf ./.git ./composer.json ./composer.lock ./package.sh \
    ./.vscode ./workspace.code-workspace ./bitbucket-pipelines.yml \
    ./phpunit.xml.dist ./.phplint-cache ./.phpunit.result.cache \
    ./psalm.xml ./tests ./dist

cd ../

# Create a zip file from the optimized plugin folder
zip -rq gravity-forms-encryption.zip ./gravity-forms-encryption
rm -rf ./gravity-forms-encryption

echo "Zip completed @ $(pwd)/gravity-forms-encryption.zip"
