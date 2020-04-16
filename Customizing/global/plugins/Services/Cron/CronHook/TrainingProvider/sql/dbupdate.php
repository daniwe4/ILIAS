<#1>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/TrainingProvider/vendor/autoload.php");
use \CaT\Plugins\TrainingProvider\Provider\ilDB as ProviderDB;

$db = new ProviderDB($ilDB);
$db->install();
?>

<#2>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/TrainingProvider/vendor/autoload.php");
use \CaT\Plugins\TrainingProvider\Trainer\ilDB as TrainerDB;

$db = new TrainerDB($ilDB);
$db->install();
?>

<#3>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/TrainingProvider/vendor/autoload.php");
use \CaT\Plugins\TrainingProvider\Tags\ilDB as TagsDB;

$db = new TagsDB($ilDB);
$db->install();
?>

<#4>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/TrainingProvider/vendor/autoload.php");
use \CaT\Plugins\TrainingProvider\Trainer\ilDB as TrainerDB;

$db = new TrainerDB($ilDB);
$db->updateTable1();
?>

<#5>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/TrainingProvider/vendor/autoload.php");
use \CaT\Plugins\TrainingProvider\Provider\ilDB as ProviderDB;

$db = new ProviderDB($ilDB);
$db->updateTable1();
?>

<#6>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/TrainingProvider/vendor/autoload.php");
use \CaT\Plugins\TrainingProvider\Provider\ilDB as ProviderDB;

$db = new ProviderDB($ilDB);
$db->updateTable2();
?>

<#7>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/TrainingProvider/vendor/autoload.php");
use \CaT\Plugins\TrainingProvider\Trainer\ilDB as TrainerDB;

$db = new TrainerDB($ilDB);
$db->updateTable2();
?>

<#8>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/TrainingProvider/vendor/autoload.php");
use \CaT\Plugins\TrainingProvider\Tags\ilDB as TagsDB;

$db = new TagsDB($ilDB);
$db->updateColumn1();
?>

<#9>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/TrainingProvider/vendor/autoload.php");
use \CaT\Plugins\TrainingProvider\ProviderAssignment\ilDB as ProvDB;

$db = new ProvDB($ilDB);
$db->install();
?>

<#10>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/TrainingProvider/vendor/autoload.php");
use \CaT\Plugins\TrainingProvider\Trainer\ilDB as TrainerDB;

$db = new TrainerDB($ilDB);
$db->updateTable3();
?>

<#11>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/TrainingProvider/vendor/autoload.php");
use \CaT\Plugins\TrainingProvider\Provider\ilDB as ProviderDB;

$db = new ProviderDB($ilDB);
$db->update3();
?>