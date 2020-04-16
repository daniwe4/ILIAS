<#1>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/vendor/autoload.php");
$db = new \CaT\Plugins\CourseMailing\RoleMapping\ilDB($ilDB);
$db->createTable();
?>
<#2>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/vendor/autoload.php");
$db = new \CaT\Plugins\CourseMailing\RoleMapping\ilDB($ilDB);
$db->createSequence();
?>
<#3>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/vendor/autoload.php");
$db = new \CaT\Plugins\CourseMailing\RoleMapping\ilDB($ilDB);
$db->createPrimaryKey();
?>
<#4>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/vendor/autoload.php");
global $DIC;
$db = new \CaT\Plugins\CourseMailing\Settings\ilDB($ilDB, $DIC->user());
$db->createTable();
?>
<#5>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/vendor/autoload.php");
global $DIC;
$db = new \CaT\Plugins\CourseMailing\Settings\ilDB($ilDB, $DIC->user());
$db->createPrimaryKey();
?>
<#6>
<?php
//init settings where there are none
require_once("Services/Object/classes/class.ilObjectFactory.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/vendor/autoload.php");
global $DIC;
$sdb = new \CaT\Plugins\CourseMailing\Settings\ilDB($ilDB, $DIC->user());

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xcml'";
$res = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($res)) {
    $obj_id = (int) $row['obj_id'];
    if (is_null($sdb->selectForObject($obj_id))) {
        $sdb->create($obj_id, 0, 0);
    }
}
?>
<#7>
<?php
;
?>

<#8>
<?php

$plug = 'CourseMailing';
$plug_id = 'xcml';
$class_name = CaT\Plugins\CourseMailing\UnboundProvider::class;

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
    $obj = new ilObjCourseMailing();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#9>
<?php
;
?>

<#10>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/vendor/autoload.php");
global $DIC;
$db = new \CaT\Plugins\CourseMailing\Settings\ilDB($ilDB, $DIC->user());
$db->update1();
?>

<#11>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/classes/class.ilObjCourseMailing.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xcml'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjCourseMailing();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#12>
<?php
;
?>

<#13>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/vendor/autoload.php");
$db = new \CaT\Plugins\CourseMailing\RoleMapping\ilDB($ilDB);
$db->createAttachmentTable();
?>

<#14>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/vendor/autoload.php");
$db = new \CaT\Plugins\CourseMailing\RoleMapping\ilDB($ilDB);
$db->createPrimaryKeyForAttachments();
?>

<#15>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/vendor/autoload.php");
$db = new \CaT\Plugins\CourseMailing\RoleMapping\ilDB($ilDB);
$db->singulateAttachmentData();
?>

<#16>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/vendor/autoload.php");
global $DIC;
$db = new \CaT\Plugins\CourseMailing\Settings\ilDB($ilDB, $DIC->user());
$db->createHistTable();
?>

<#17>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/vendor/autoload.php");
global $DIC;
$db = new \CaT\Plugins\CourseMailing\Settings\ilDB($ilDB, $DIC->user());
$db->createHistPrimaryKey();
?>

<#18>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/vendor/autoload.php");
global $DIC;
$db = new \CaT\Plugins\CourseMailing\Settings\ilDB($ilDB, $DIC->user());
$db->createHistSequence();
?>

<#19>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMailing/classes/class.ilObjCourseMailing.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xcml'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjCourseMailing();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#20>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xcml_invited_users')) {
    $fields = [
        'id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'usr_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'obj_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'added_by' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'added_at' => [
            'type' => 'timestamp',
            'notnull' => true
        ],
        'invite_by' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
            'default' => null
        ],
        'invite_at' => [
            'type' => 'timestamp',
            'notnull' => false,
            'default' => null
        ],
        'rejected_by' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => false,
            'default' => null
        ],
        'rejected_at' => [
            'type' => 'timestamp',
            'notnull' => false,
            'default' => null
        ],
        'hash' => [
            'type' => 'clob',
            'notnull' => false,
            'default' => null
        ],
        'deleted' => [
            'type' => 'integer',
            'length' => 1,
            'notnull' => false,
            'default' => 0
        ],
    ];
    $db->createTable('xcml_invited_users', $fields);
}
?>

<#21>
<?php
global $DIC;
$db = $DIC['ilDB'];

try {
    $db->addPrimaryKey('xcml_invited_users', array("id"));
} catch (\PDOException $e) {
    $db->dropPrimaryKey('xcml_invited_users');
    $db->addPrimaryKey('xcml_invited_users', array("id"));
}
?>

<#22>
<?php
global $DIC;
$db = $DIC['ilDB'];

if (!$db->sequenceExists('xcml_invited_users')) {
    $db->createSequence('xcml_invited_users');
}
?>

<#23>
<?php
global $DIC;
$db = $DIC['ilDB'];
if ($db->tableColumnExists('xcml_rolemappings', 'role_title')) {
    $db->dropTableColumn('xcml_rolemappings', 'role_title');
}
?>

<#24>
<?php
global $DIC;
$db = $DIC['ilDB'];
$q = 'SELECT id FROM xcml_rolemappings WHERE mail_template_id = 0';
$res = $db->query($q);
while ($row = $db->fetchAssoc($res)) {
    $id = $row['id'];
    $q = "DELETE FROM xcml_attachment_maps WHERE mapping_id = " . $id;
    $db->manipulate($q);
}
?>

<#25>
<?php
global $DIC;
$db = $DIC['ilDB'];
$q = 'SELECT id FROM xcml_rolemappings WHERE mail_template_id = 0';
$res = $db->query($q);
while ($row = $db->fetchAssoc($res)) {
    $id = $row['id'];
    $q = "DELETE FROM xcml_rolemappings WHERE id = " . $id;
    $db->manipulate($q);
}
?>