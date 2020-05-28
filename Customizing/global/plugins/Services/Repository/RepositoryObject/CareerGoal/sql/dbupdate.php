<#1>
<?php
global $ilUser;

$settings_db = new \CaT\Plugins\CareerGoal\Settings\ilDB($ilDB, $ilUser);
$settings_db->install();
?>

<#2>
<?php
global $ilUser;

$settings_db = new \CaT\Plugins\CareerGoal\Requirements\ilDB($ilDB, $ilUser);
$settings_db->install();
?>

<#3>
<?php
global $ilUser;

$settings_db = new \CaT\Plugins\CareerGoal\Observations\ilDB($ilDB, $ilUser);
$settings_db->install();
?>

<#4>
<?php
global $ilUser;

$settings_db = new \CaT\Plugins\CareerGoal\Settings\ilDB($ilDB, $ilUser);
$settings_db->renameColumns();
?>