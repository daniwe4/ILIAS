#!/bin/bash

#plugin path from ilias-root:
PLUGINPATH='Customizing/global/plugins/Services/Repository/RepositoryObject/CancellationFeeReport';
SCRIPT_PATH=$(dirname "$0");
cd $SCRIPT_PATH;
# note: no more parameters


#first param is path to ilias installation
if [ $1 ] ; then
	cd $1;
	echo;
	echo 'now running ILIAS tests in ' $1;
	echo $PLUGINPATH;
	phpunit --bootstrap ./$PLUGINPATH/vendor/autoload.php $PLUGINPATH/tests
else
	phpunit --bootstrap ./vendor/autoload.php --exclude-group needsInstalledILIAS tests;
fi