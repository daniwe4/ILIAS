<#1>
<?php
$db = new \CaT\Plugins\DataChanges\Config\Log\ilDB($ilDB);
$db->createTable();
?>
<#2>
<?php
$db = new \CaT\Plugins\DataChanges\Config\Log\ilDB($ilDB);
$db->createPrimaryKey();
?>
<#3>
<?php
$db = new \CaT\Plugins\DataChanges\Config\Log\ilDB($ilDB);
$db->createSequence();
?>
<#4>
<?php
global $DIC;
$db = new \CaT\Plugins\DataChanges\Config\UDF\ilDB($ilDB, $DIC->user());
$db->createTable();
?>
<#5>
<?php
global $DIC;
$db = new \CaT\Plugins\DataChanges\Config\UDF\ilDB($ilDB, $DIC->user());
$db->createPrimaryKey();
?>
