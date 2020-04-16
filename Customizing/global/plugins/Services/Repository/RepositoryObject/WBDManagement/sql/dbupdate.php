<#1>
<?php
require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/WBDManagement/vendor/autoload.php";
$db = new \CaT\Plugins\WBDManagement\Settings\ilDB($ilDB);
$db->createTable();
?>
<#2>
<?php
require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/WBDManagement/vendor/autoload.php";
$db = new \CaT\Plugins\WBDManagement\Settings\ilDB($ilDB);
$db->createPrimaryKey();
?>
<#3>
<?php
require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/WBDManagement/vendor/autoload.php";
$db = new \CaT\Plugins\WBDManagement\Settings\ilDB($ilDB);
$db->createSequence();
?>
<#4>
<?php
require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/WBDManagement/vendor/autoload.php";
$db = new \CaT\Plugins\WBDManagement\Settings\ilDB($ilDB);
$db->update1();
?>
<#5>
<?php
require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/WBDManagement/vendor/autoload.php";
$db = new \CaT\Plugins\WBDManagement\GutBeraten\ilDB($ilDB);
$db->createTable();
?>
<#6>
<?php
require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/WBDManagement/vendor/autoload.php";
$db = new \CaT\Plugins\WBDManagement\GutBeraten\ilDB($ilDB);
$db->createPrimaryKey();
?>
<#7>
<?php
require_once "Customizing/global/plugins/Services/Repository/RepositoryObject/WBDManagement/vendor/autoload.php";
$db = new \CaT\Plugins\WBDManagement\Settings\ilDB($ilDB);
$db->update2();
?>
