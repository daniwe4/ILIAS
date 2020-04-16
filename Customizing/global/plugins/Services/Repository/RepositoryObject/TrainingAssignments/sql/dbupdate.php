<#1>
<?php
require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/TrainingAssignments/vendor/autoload.php";
$db = new \CaT\Plugins\TrainingAssignments\Settings\ilDB($ilDB);
$db->createTable();
?>
<#2>
<?php
require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/TrainingAssignments/vendor/autoload.php";
$db = new \CaT\Plugins\TrainingAssignments\Settings\ilDB($ilDB);
$db->createPrimaryKey();
?>
