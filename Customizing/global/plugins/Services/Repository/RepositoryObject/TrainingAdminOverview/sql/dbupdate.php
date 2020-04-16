<#1>
<?php
require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/TrainingAdminOverview/vendor/autoload.php";
$db = new \CaT\Plugins\TrainingAdminOverview\Settings\ilDB($ilDB);
$db->createTable();
?>
<#2>
<?php
require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/TrainingAdminOverview/vendor/autoload.php";
$db = new \CaT\Plugins\TrainingAdminOverview\Settings\ilDB($ilDB);
$db->createPrimaryKey();
?>
