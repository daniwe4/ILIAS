<#1>
<?php
require_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$type_id = ilDBUpdateNewObjectType::addNewType(
    CaT\Plugins\TrainerOperations\ObjTrainerOperations::PLUGIN_ID,
    CaT\Plugins\TrainerOperations\ObjTrainerOperations::PLUGIN_NAME
);
$order = 8500;

$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation(
    CaT\Plugins\TrainerOperations\ObjTrainerOperations::OP_EDIT_OWN_CALENDARS,
    'Edit Own Calendars',
    'object',
    $order++
);
ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);

$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation(
    CaT\Plugins\TrainerOperations\ObjTrainerOperations::OP_EDIT_GENERAL_CALENDARS,
    'Edit General Calendars',
    'object',
    $order++
);
ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);

$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation(
    CaT\Plugins\TrainerOperations\ObjTrainerOperations::OP_SEE_UNASSIGNED,
    'See Trainings without Assigned Tutors',
    'object',
    $order++
);
ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);
?>

<#2>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/TrainerOperations/vendor/autoload.php");
ilOrgUnitOperationContextQueries::registerNewContext(
    CaT\Plugins\TrainerOperations\ObjTrainerOperations::PLUGIN_ID,
    ilOrgUnitOperationContext::CONTEXT_OBJECT
);

ilOrgUnitOperationQueries::registerNewOperation(
    CaT\Plugins\TrainerOperations\ObjTrainerOperations::ORGU_OP_SEE_OTHER_CALENDARS,
    'See Other Calendars',
    CaT\Plugins\TrainerOperations\ObjTrainerOperations::PLUGIN_ID
);
?>

<#3>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/TrainerOperations/vendor/autoload.php");
$settings_db = new CaT\Plugins\TrainerOperations\Settings\ilDB($ilDB);
$settings_db->createTable();
?>

<#4>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/TrainerOperations/vendor/autoload.php");
$settings_db = new CaT\Plugins\TrainerOperations\Settings\ilDB($ilDB);
$settings_db->createPrimaryKeys();
?>

<#5>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/TrainerOperations/vendor/autoload.php");
$settings_db = new CaT\Plugins\TrainerOperations\UserSettings\ilDB($ilDB);
$settings_db->createTable();
?>

<#6>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/TrainerOperations/vendor/autoload.php");
$settings_db = new CaT\Plugins\TrainerOperations\UserSettings\ilDB($ilDB);
$settings_db->createPrimaryKeys();
?>

<#7>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/TrainerOperations/vendor/autoload.php");
$settings_db = new CaT\Plugins\TrainerOperations\UserSettings\ilDB($ilDB);
$settings_db->createSequenceTable();
?>

<#8>
<?php
require_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$type_id = ilDBUpdateNewObjectType::getObjectTypeId(
    CaT\Plugins\TrainerOperations\ObjTrainerOperations::PLUGIN_ID
);
$order = 8504;

$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation(
    CaT\Plugins\TrainerOperations\ObjTrainerOperations::OP_SEE_GENERAL,
    'See General Calendars',
    'object',
    $order++
);
ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);
?>

<#9>
<?php
require_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$order = 8500;
$ops = [
    CaT\Plugins\TrainerOperations\ObjTrainerOperations::OP_EDIT_OWN_CALENDARS,
    CaT\Plugins\TrainerOperations\ObjTrainerOperations::OP_SEE_GENERAL,
    CaT\Plugins\TrainerOperations\ObjTrainerOperations::OP_EDIT_GENERAL_CALENDARS,
    CaT\Plugins\TrainerOperations\ObjTrainerOperations::OP_SEE_UNASSIGNED
];
foreach ($ops as $op) {
    ilDBUpdateNewObjectType::updateOperationOrder($op, $order);
    $order++;
}
?>
