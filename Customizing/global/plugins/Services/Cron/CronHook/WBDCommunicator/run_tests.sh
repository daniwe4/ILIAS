#!/bin/bash

#plugin path from ilias-root:
PLUGINPATH='Customizing/global/plugins/Services/Cron/CronHook/WBDCommunicator';
SCRIPT_PATH=$(dirname "$0");
cd $SCRIPT_PATH;

#first param is path to ilias installation
if [ $1 ] ;
then
	cd $1;
	echo;
	echo 'now running ILIAS tests in ' $1;
	./$PLUGINPATH/vendor/phpunit/phpunit/phpunit --bootstrap ./$PLUGINPATH/vendor/autoload.php $PLUGINPATH/tests
else
	# note: no more parameters
	./vendor/phpunit/phpunit/phpunit --bootstrap ./vendor/autoload.php --exclude-group needsInstalledILIAS tests;
fi