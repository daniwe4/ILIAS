<#1>
<?php



$request_db = new CaT\Plugins\CourseCreation\ilRequestDB($ilDB);
$request_db->createTable();

?>
<#2>
<?php



$request_db = new CaT\Plugins\CourseCreation\ilRequestDB($ilDB);
$request_db->createSequence();

?>
<#3>
<?php



$request_db = new CaT\Plugins\CourseCreation\ilRequestDB($ilDB);
$request_db->updateTable1();

?>
<#4>
<?php


global $DIC;
$request_db = new CaT\Plugins\CourseCreation\CreationSettings\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$request_db->createTable();
?>
<#5>
<?php


global $DIC;
$request_db = new CaT\Plugins\CourseCreation\CreationSettings\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$request_db->createSequence();
?>
<#6>
<?php


global $DIC;
$request_db = new CaT\Plugins\CourseCreation\CreationSettings\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$request_db->addPrimaryKey();
?>