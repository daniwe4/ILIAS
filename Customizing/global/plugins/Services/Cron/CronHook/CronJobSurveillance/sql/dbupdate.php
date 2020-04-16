<#1>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/CronJobSurveillance/vendor/autoload.php");
$db = new CaT\Plugins\CronJobSurveillance\Config\ilDB($ilDB);
$db->createTable();
?>
<#2>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/CronJobSurveillance/vendor/autoload.php");
$db = new CaT\Plugins\CronJobSurveillance\Config\ilDB($ilDB);
$db->createPrimaryKey();
?>
<#3>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/CronJobSurveillance/vendor/autoload.php");
$db = new CaT\Plugins\CronJobSurveillance\Config\ilDB($ilDB);
$db->createHistTable();
?>
<#4>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/CronJobSurveillance/vendor/autoload.php");
$db = new CaT\Plugins\CronJobSurveillance\Config\ilDB($ilDB);
$db->createHistPrimaryKey();
?>
<#5>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/CronJobSurveillance/vendor/autoload.php");
$db = new CaT\Plugins\CronJobSurveillance\Config\ilDB($ilDB);
$db->createHistSequence();
?>
<#6>
<?php
require_once("Customizing/global/plugins/Services/Cron/CronHook/CronJobSurveillance/vendor/autoload.php");
$db = new CaT\Plugins\CronJobSurveillance\Config\ilDB($ilDB);
$db->update1();
?>