#!/bin/bash

# Load the general plugins
source ./CI/packaging/config/.global
GLOBAL_PLUGINS=$PLUGINS

# Walk through the configs and copy the repository to a local
for CONFIG in $(ls ./CI/packaging/config)
do

  NOW=$(date +'%d.%m.%Y %I:%M:%S')
  echo "[$NOW] Building $CONFIG"

  source "./CI/packaging/config/$CONFIG"
  mkdir -p "./CI/packaging/packages/$CONFIG"

  # copy the main files

  NOW=$(date +'%d.%m.%Y %I:%M:%S')
  echo "[$NOW] Copying main files"

  rsync -av --progress -q ./ "./CI/packaging/packages/$CONFIG" --exclude .git --exclude CI --exclude Customizing --exclude packaging --exclude 'tests*' --exclude 'test*'

  # make the needed folders
  mkdir -p "./CI/packaging/packages/$CONFIG/Customizing/global/plugins/Modules/OrgUnit/OrgUnitExtension"
  mkdir -p "./CI/packaging/packages/$CONFIG/Customizing/global/plugins/Services"
  mkdir -p "./CI/packaging/packages/$CONFIG/Customizing/global/plugins/Services/Cron/CronHook"
  mkdir -p "./CI/packaging/packages/$CONFIG/Customizing/global/plugins/Services/EventHandling/EventHook"
  mkdir -p "./CI/packaging/packages/$CONFIG/Customizing/global/plugins/Services/OrgUnit/OrgUnitTypeHook"
  mkdir -p "./CI/packaging/packages/$CONFIG/Customizing/global/plugins/Services/Repository/RepositoryObject"
  mkdir -p "./CI/packaging/packages/$CONFIG/Customizing/global/plugins/Services/COPage/PageComponent"

  # copy the global plugins

  NOW=$(date +'%d.%m.%Y %I:%M:%S')
  echo "[$NOW] Copying global plugins"

  for PLUGIN in $GLOBAL_PLUGINS
  do
    cp -r "./Customizing/global/plugins/$PLUGIN" "./CI/packaging/packages/$CONFIG/Customizing/global/plugins/$PLUGIN"
  done

  # copy the client plugins

  NOW=$(date +'%d.%m.%Y %I:%M:%S')
  echo "[$NOW] Copying client plugins"

  for PLUGIN in $PLUGINS
  do
    cp -r "./Customizing/global/plugins/$PLUGIN" "./CI/packaging/packages/$CONFIG/Customizing/global/plugins/$PLUGIN"
  done

  # Rename the plugins
  NOW=$(date +'%d.%m.%Y %I:%M:%S')
  echo "[$NOW] Renaming plugins"

  if [ ! -z "$RENAME" ]
  then
    for RENAME_PLUGIN in $RENAME
    do
      FROM="$(cut -d'=' -f1 <<<$RENAME_PLUGIN)"
      TO="$(cut -d'=' -f2 <<<$RENAME_PLUGIN)"

      mv "./CI/packaging/packages/$CONFIG/Customizing/global/plugins/$FROM" "./CI/packaging/packages/$CONFIG/Customizing/global/plugins/$TO"
    done
  fi

  # clone the skin
  NOW=$(date +'%d.%m.%Y %I:%M:%S')
  echo "[$NOW] Cloning skin"

  git clone --single-branch --branch $SKIN git@github.com:conceptsandtraining/ilias-skins60.git "./CI/packaging/packages/$CONFIG/Customizing/global/common/skin"
  rm -rf ./CI/packaging/packages/$CONFIG/Customizing/global/common/skin/.git

  # cleanup
	for TESTFOLDER in $(find ./CI/packaging/packages/$CONFIG/ -type d -name "test*")
	do
		rm -rf $TESTFOLDER
	done
	rm ./CI/packaging/packages/$CONFIG/.travis.yml
	rm ./CI/packaging/packages/$CONFIG/.gitignore
	rm ./CI/packaging/packages/$CONFIG/.phpunit.results.cache

  # reset local config for next iteration
  RENAME=""
  PLUGINS=""

  NOW=$(date +'%d.%m.%Y %I:%M:%S')
  echo "[$NOW] Finished building $CONFIG"

done
