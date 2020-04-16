<#1>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$booking_db = new CaT\Plugins\BookingModalities\Settings\Booking\ilDB($ilDB);
$storno_db = new CaT\Plugins\BookingModalities\Settings\Storno\ilDB($ilDB);
$member_db = new CaT\Plugins\BookingModalities\Settings\Member\ilDB($ilDB);
$waitinglist_db = new CaT\Plugins\BookingModalities\Settings\Waitinglist\ilDB($ilDB);
$approve_role_db = new CaT\Plugins\BookingModalities\Settings\ApproveRole\ilDB($ilDB);

$booking_db->createTable1();
$storno_db->createTable1();
$member_db->createTable1();
$waitinglist_db->createTable1();
$approve_role_db->createTable1();
?>

<#2>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$booking_db = new CaT\Plugins\BookingModalities\Settings\Booking\ilDB($ilDB);

$booking_db->createBookingPrimaryKey();
?>

<#3>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$approve_role_db = new CaT\Plugins\BookingModalities\Settings\ApproveRole\ilDB($ilDB);

$approve_role_db->createApproversPrimaryKey();
?>

<#4>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$storno_db = new CaT\Plugins\BookingModalities\Settings\Storno\ilDB($ilDB);

$storno_db->createStornoPrimaryKey();
?>

<#5>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$member_db = new CaT\Plugins\BookingModalities\Settings\Member\ilDB($ilDB);

$member_db->createMemberPrimaryKey();
?>

<#6>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$waitinglist_db = new CaT\Plugins\BookingModalities\Settings\Waitinglist\ilDB($ilDB);

$waitinglist_db->createWaitinglistPrimaryKey();
?>

<#7>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$selectable_roles_db = new CaT\Plugins\BookingModalities\Settings\SelectableRoles\ilDB($ilDB);

$selectable_roles_db->createTable();
?>

<#8>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$approve_role_db = new CaT\Plugins\BookingModalities\Settings\ApproveRole\ilDB($ilDB);

$approve_role_db->updateTabe1();
?>

<#9>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$approve_role_db = new CaT\Plugins\BookingModalities\Settings\ApproveRole\ilDB($ilDB);

$approve_role_db->modifyApproversPrimaryKey();
?>

<#10>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$approve_role_db = new CaT\Plugins\BookingModalities\Settings\SelectableReasons\ilDB($ilDB);

$approve_role_db->createTable();
?>

<#11>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$approve_role_db = new CaT\Plugins\BookingModalities\Settings\SelectableReasons\ilDB($ilDB);

$approve_role_db->createPrimaryKey();
?>

<#12>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$approve_role_db = new CaT\Plugins\BookingModalities\Settings\SelectableReasons\ilDB($ilDB);

$approve_role_db->createSequence();
?>

<#13>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$storno_db = new CaT\Plugins\BookingModalities\Settings\Storno\ilDB($ilDB);

$storno_db->update1();
?>

<#14>
<?php

global $DIC;
$db = $DIC->database();
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($db, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/classes/class.ilObjBookingModalities.php");
$query = $db->query("SELECT obj_id FROM object_data WHERE type = 'xbkm'");
while ($res = $db->fetchAssoc($query)) {
    $obj = new ilObjBookingModalities();
    $obj->setId($res["obj_id"]);
    $provider_db->createSeparatedUnboundProvider($obj, "crs", CaT\Plugins\BookingModalities\UnboundProvider::class, "Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/classes/UnboundProvider.php");
}

?>

<#15>
<?php

global $DIC;
$db = $DIC->database();
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($db, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/classes/class.ilObjBookingModalities.php");
$query = $db->query("SELECT object_data.obj_id, object_reference.ref_id FROM object_data JOIN object_reference ON object_data.obj_id = object_reference.obj_id WHERE type = 'xbkm'");
while ($res = $db->fetchAssoc($query)) {
    $obj = new ilObjBookingModalities();
    $obj->setId($res["obj_id"]);
    $obj->setRefId($res["ref_id"]);
    foreach ($provider_db->providersFor($obj) as $provider) {
        $provider_db->update($provider);
    }
}

?>
<#16>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$docs_db = new CaT\Plugins\BookingModalities\Settings\DownloadableDocument\ilDB($ilDB);
$docs_db->install();
?>

<#17>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$storno_db = new CaT\Plugins\BookingModalities\Settings\Storno\ilDB($ilDB);

$storno_db->update2();
?>

<#18>
<?php

$plug = 'BookingModalities';
$plug_id = 'xbkm';
$class_name = CaT\Plugins\BookingModalities\UnboundProvider::class;

global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$req = "Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/class.ilObj$plug.php";
require_once($req);
$class_name_sql = str_replace('\\', '\\\\', $class_name);

$query = "DELETE FROM ente_prv_cmps WHERE id IN (SELECT id FROM ente_prvs WHERE class_name = '$class_name_sql')";
$ilDB->query($query);

$query = "DELETE FROM ente_prvs WHERE class_name = '$class_name_sql'";
$ilDB->query($query);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = '$plug_id'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjBookingModalities();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#19>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/classes/class.ilObjBookingModalities.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xbkm'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjBookingModalities();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#20>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/classes/class.ilObjBookingModalities.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xbkm'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjBookingModalities();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#21>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/classes/class.ilObjBookingModalities.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$class_name = CaT\Plugins\BookingModalities\SharedUnboundProvider::class;
$path = ILIAS_ABSOLUTE_PATH
    . "/Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/classes/SharedUnboundProvider.php";

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xbkm'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjBookingModalities();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
    $provider = $provider_db->createSharedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#22>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$booking_db = new CaT\Plugins\BookingModalities\Settings\Booking\ilDB($ilDB);
$booking_db->update1();
?>

<#23>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/classes/class.ilObjBookingModalities.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xbkm'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjBookingModalities();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#24>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$booking_db = new CaT\Plugins\BookingModalities\Settings\SelectableRoles\ilDB($ilDB);
$booking_db->update1();
?>

<#25>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$booking_db = new CaT\Plugins\BookingModalities\Settings\ApproveRole\ilDB($ilDB);
$booking_db->updateTable2();
?>

<#26>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$booking_db = new CaT\Plugins\BookingModalities\Settings\Booking\ilDB($ilDB);

$booking_db->update2();
?>

<#27>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$booking_db = new CaT\Plugins\BookingModalities\Settings\Booking\ilDB($ilDB);

$booking_db->update3();
?>

<#28>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/classes/class.ilObjBookingModalities.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xbkm'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjBookingModalities();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>
<#29>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$booking_db = new CaT\Plugins\BookingModalities\Reminder\ilDB($ilDB);

$booking_db->createTable();
?>
<#30>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$booking_db = new CaT\Plugins\BookingModalities\Reminder\ilDB($ilDB);

$booking_db->createSequence();
?>
<#31>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/vendor/autoload.php");
$booking_db = new CaT\Plugins\BookingModalities\Reminder\ilDB($ilDB);

$booking_db->createPrimaryKey();
?>

<#32>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BookingModalities/classes/class.ilObjBookingModalities.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xbkm'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjBookingModalities();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>