#!/bin/bash

DEPLOY_SCRIPT="deploy-$1.sh"
OWNER=$2
GHREPO=$3

# check if deploy script exists
if [ ! -f "./CI/packaging/$DEPLOY_SCRIPT" ]
then
  echo -e "\033[1mERROR:\033[0m"
  echo -e "\tCan't find a suitable deploy script."
  exit 0
fi

# first build
source ./CI/packaging/build.sh

NOW=$(date +'%d.%m.%Y %I:%M:%S')
echo "[$NOW] Starting deployment with $DEPLOY_SCRIPT"

# then deploy
source ./CI/packaging/$DEPLOY_SCRIPT $OWNER $GHREPO

NOW=$(date +'%d.%m.%Y %I:%M:%S')
echo "[$NOW] Finished deployment with $DEPLOY_SCRIPT"
