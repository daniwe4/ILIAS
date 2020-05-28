<#1>
<?php
global $DIC;
$user = $DIC->user();

$career_goal_db = new \CaT\Plugins\CareerGoal\Settings\ilDB($ilDB, $user);
$settings_db = new \CaT\Plugins\TalentAssessment\Settings\ilDB($ilDB, $user, $career_goal_db);
$settings_db->install();
?>

<#2>
<?php
global $DIC;
$user = $DIC->user();

$settings_db = new \CaT\Plugins\TalentAssessment\Observer\ilDB($ilDB, $user);
$settings_db->createLocalRoleTemplate("il_xtas_observer", "");
?>

<#3>
<?php
global $DIC;
$user = $DIC->user();

$b = new \CaT\Plugins\CareerGoal\Observations\ilDB($ilDB, $user);
$settings_db = new \CaT\Plugins\TalentAssessment\Observations\ilDB($ilDB, $user, $b);
$settings_db->install();
?>

<#4>
<?php
global $DIC;
$user = $DIC->user();

$career_goal_db = new \CaT\Plugins\CareerGoal\Settings\ilDB($ilDB, $user);
$settings_db = new \CaT\Plugins\TalentAssessment\Settings\ilDB($ilDB, $user, $career_goal_db);
$settings_db->install();
?>

<#5>
<?php
global $DIC;
$user = $DIC->user();

$b = new \CaT\Plugins\CareerGoal\Observations\ilDB($ilDB, $user);
$settings_db = new \CaT\Plugins\TalentAssessment\Observations\ilDB($ilDB, $user, $b);
$settings_db->updateColumns();
?>

<#6>
<?php
;
?>

<#7>
<?php
global $DIC;
$user = $DIC->user();

$settings_db = new \CaT\Plugins\TalentAssessment\Observer\ilDB($ilDB, $user);
$settings_db->setRoleFolder("il_xtas_observer", 8);
?>

<#8>
<?php
global $DIC;
$user = $DIC->user();

$settings_db = new \CaT\Plugins\TalentAssessment\Observer\ilDB($ilDB, $user);
$settings_db->setDefaultPermissions("il_xtas_observer", 8, array("visible", "read", "edit_observation"));
?>

<#9>
<?php
global $DIC;
$user = $DIC->user();

$query = "UPDATE object_data\n"
		."SET title = 'pl_xtas_observer'\n"
		."WHERE title = 'il_xtas_observer'";
$ilDB->query($query);
?>

<#10>
<?php
global $DIC;
$user = $DIC->user();

$career_goal_db = new \CaT\Plugins\CareerGoal\Settings\ilDB($ilDB, $user);
$settings_db = new \CaT\Plugins\TalentAssessment\Settings\ilDB($ilDB, $user, $career_goal_db);
$settings_db->update1();
?>