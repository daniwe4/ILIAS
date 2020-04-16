<#1>
<?php

require_once("Customizing/global/plugins/Services/Cron/CronHook/CourseCreation/vendor/autoload.php");

$request_db = new CaT\Plugins\CourseCreation\ilRequestDB($ilDB);
$request_db->createTable();

?>
<#2>
<?php

require_once("Customizing/global/plugins/Services/Cron/CronHook/CourseCreation/vendor/autoload.php");

$request_db = new CaT\Plugins\CourseCreation\ilRequestDB($ilDB);
$request_db->createSequence();

?>
<#3>
<?php

require_once("Customizing/global/plugins/Services/Cron/CronHook/CourseCreation/vendor/autoload.php");

$request_db = new CaT\Plugins\CourseCreation\ilRequestDB($ilDB);
$request_db->updateTable1();

?>
<#4>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/CourseCreation/vendor/autoload.php");

global $DIC;
$request_db = new CaT\Plugins\CourseCreation\CreationSettings\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$request_db->createTable();
?>
<#5>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/CourseCreation/vendor/autoload.php");

global $DIC;
$request_db = new CaT\Plugins\CourseCreation\CreationSettings\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$request_db->createSequence();
?>
<#6>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/CourseCreation/vendor/autoload.php");

global $DIC;
$request_db = new CaT\Plugins\CourseCreation\CreationSettings\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$request_db->addPrimaryKey();
?>