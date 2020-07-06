#!/bin/bash

OWNER=$1
GHREPO=$2

# Load the general plugins
source ./CI/packaging/config/.global
GLOBAL_PLUGINS=$PLUGINS

# current dir
DIR=$(pwd)

# Walk through the configs and copy the repository to a local
for CONFIG in $(ls ./CI/packaging/config)
do
  NOW=$(date +'%d.%m.%Y %I:%M:%S')
  echo "[$NOW] Building archive for $CONFIG"

  source "./CI/packaging/config/$CONFIG"

  # pack and delete temp files
  cd "./CI/packaging/packages/"
  tar -zcvf "$CONFIG-$TRAVIS_TAG.tar.gz" "$CONFIG"
  cd $PWD
  rm -rf "./CI/packaging/packages/$CONFIG"

  # add package to TMS repo
  NOW=$(date +'%d.%m.%Y %I:%M:%S')
  echo "[$NOW] Uploading archive"
  ./CI/packaging/upload-asset.sh github_api_token=$CAT_GITHUB_TOKEN owner=$OWNER repo=$GHREPO tag=$TRAVIS_TAG filename="./CI/packaging/packages/$CONFIG-$TRAVIS_TAG.tar.gz"

  # remove package
  rm "./CI/packaging/packages/$CONFIG-$TRAVIS_TAG.tar.gz"

  NOW=$(date +'%d.%m.%Y %I:%M:%S')
  echo "[$NOW] Finished building archive for $CONFIG"

done
