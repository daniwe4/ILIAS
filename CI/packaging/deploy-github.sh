#!/bin/bash

OWNER=$1
GHREPO=$2

# current dir
DIR=$(pwd)

# Load the general plugins
source ./CI/packaging/config/.global
GLOBAL_PLUGINS=$PLUGINS

# Walk through the configs and copy the repository to a local
for CONFIG in $(ls ./CI/packaging/config)
do
  NOW=$(date +'%d.%m.%Y %I:%M:%S')
  echo "[$NOW] Building repo for $CONFIG"

  cd "./CI/packaging/packages/$CONFIG"

	# build local repo
	git init
	git add .
	git commit -am "${CONFIG} release ${TRAVIS_TAG}" --quiet

  # get remote repository
  git remote add origin git@github.com:conceptsandtraining/ilias-tms6-packages.git
  git fetch origin

  # check the branch
  BRANCH=$(git branch -a | egrep "remotes/origin/${CONFIG}$")
  if [ -z "$BRANCH" ]
  then
    git checkout -b $CONFIG
  else
    git checkout $CONFIG
  fi

	# cleanup branch
	rm -rf *
	git commit -am "Cleanup to prepare for release" --quiet

	# merge
	git merge master --allow-unrelated-histories --quiet
	git commit -am "${CONFIG} release ${TRAVIS_TAG}" --quiet
	git push origin $CONFIG --quiet

  # build tag
  git tag -a "$CONFIG-$TRAVIS_TAG" -m "Release for $CONFIG-$TRAVIS_TAG"
  git push origin --tags --quiet

  cd $PWD

  NOW=$(date +'%d.%m.%Y %I:%M:%S')
  echo "[$NOW] Finished building repo for $CONFIG"
done
