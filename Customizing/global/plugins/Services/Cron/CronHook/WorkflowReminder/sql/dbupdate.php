<#1>
<?php
require_once "Customizing/global/plugins/Services/Cron/CronHook/WorkflowReminder/vendor/autoload.php";
global $DIC;
$db = new CaT\Plugins\WorkflowReminder\NotFinalized\Log\ilDB($DIC["ilDB"]);
$db->createLogTable();
?>
<#2>
<?php
require_once "Customizing/global/plugins/Services/Cron/CronHook/WorkflowReminder/vendor/autoload.php";
global $DIC;
$db = new CaT\Plugins\WorkflowReminder\NotFinalized\Log\ilDB($DIC["ilDB"]);
$db->createSequence();
?>
<#3>
<?php
require_once "Customizing/global/plugins/Services/Cron/CronHook/WorkflowReminder/vendor/autoload.php";
global $DIC;
$db = new CaT\Plugins\WorkflowReminder\NotFinalized\Log\ilDB($DIC["ilDB"]);
$db->createPrimaryKey();
?>