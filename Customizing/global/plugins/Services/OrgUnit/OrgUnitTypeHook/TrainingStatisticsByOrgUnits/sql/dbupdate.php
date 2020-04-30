<#1>
<?php

global $DIC;
$db = new CaT\Plugins\TrainingStatisticsByOrgUnits\Settings\ilDB($DIC["ilDB"]);
$db->createTable();
?>

<#2>
<?php

global $DIC;
$db = new CaT\Plugins\TrainingStatisticsByOrgUnits\Settings\ilDB($DIC["ilDB"]);
$db->addPrimaryKey();
?>

<#3>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('copy');
ilDBUpdateNewObjectType::deleteRBACOperation('xtou', $ops_id);
?>

<#4>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('xtou_settings', 'is_global')) {
    $db->addTableColumn(
        'xtou_settings',
        'is_global',
        [
            "type" => "integer",
            "length" => 1,
            "default" => 0
        ]
    );
}
?>