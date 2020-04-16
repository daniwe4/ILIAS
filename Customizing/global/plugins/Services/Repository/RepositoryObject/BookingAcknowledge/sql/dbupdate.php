<#1>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingAcknowledge/vendor/autoload.php");
$db = new CaT\Plugins\BookingAcknowledge\Acknowledgments\ilDB($ilDB);
$db->createTable();
?>

<#2>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingAcknowledge/vendor/autoload.php");
$db = new CaT\Plugins\BookingAcknowledge\Acknowledgments\ilDB($ilDB);
$db->createPrimaryKey();
?>

<#3>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingAcknowledge/vendor/autoload.php");
$db = new CaT\Plugins\BookingAcknowledge\Acknowledgments\ilDB($ilDB);
$db->createSequenceRequests();
?>

<#4>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingAcknowledge/vendor/autoload.php");
$db = new CaT\Plugins\BookingAcknowledge\Acknowledgments\ilDB($ilDB);
$db->updateTable1();
?>

<#5>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingAcknowledge/vendor/autoload.php");
ilOrgUnitOperationContextQueries::registerNewContext(
    CaT\Plugins\BookingAcknowledge\BookingAcknowledge::ORGU_CONTEXT,
    ilOrgUnitOperationContext::CONTEXT_OBJECT
);
?>

<#6>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingAcknowledge/vendor/autoload.php");
ilOrgUnitOperationQueries::registerNewOperation(
    CaT\Plugins\BookingAcknowledge\BookingAcknowledge::ORGU_OP_SEE_USERBOOKINGS,
    'See Userbookings',
    CaT\Plugins\BookingAcknowledge\BookingAcknowledge::ORGU_CONTEXT
);
ilOrgUnitOperationQueries::registerNewOperation(
    CaT\Plugins\BookingAcknowledge\BookingAcknowledge::ORGU_OP_ACKNOWLEDGE,
    'Acknowledge/Decline Userbookings',
    CaT\Plugins\BookingAcknowledge\BookingAcknowledge::ORGU_CONTEXT
);
?>

<#7>
<?php
require_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$type_id = ilDBUpdateNewObjectType::addNewType('xack', 'BookingAcknowledge');
$order = 8400;

$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation(
    CaT\Plugins\BookingAcknowledge\BookingAcknowledge::OP_ACKNOWLEDGE,
    'Acknowledge Accomodations',
    'object',
    $order++
);
ilDBUpdateNewObjectType::addRBACOperation($type_id, $new_ops_id);

?>
