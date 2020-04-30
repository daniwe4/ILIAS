<#1>
<?php

$db = new CaT\Plugins\CourseMember\LPOptions\ilDB($ilDB);
$db->createTable();
?>

<#2>
<?php

$db = new CaT\Plugins\CourseMember\LPOptions\ilDB($ilDB);
$db->createPrimaryKey();
?>

<#3>
<?php

$db = new CaT\Plugins\CourseMember\LPOptions\ilDB($ilDB);
$db->createSequence();
?>

<#4>
<?php

global $DIC;

$db = new CaT\Plugins\CourseMember\Members\ilDB($ilDB, $DIC->user());
$db->createTable();
?>

<#5>
<?php

global $DIC;

$db = new CaT\Plugins\CourseMember\Members\ilDB($ilDB, $DIC->user());
$db->createPrimaryKey();
?>

<#6>
<?php

global $DIC;

$db = new CaT\Plugins\CourseMember\Settings\ilDB($ilDB);
$db->createTable();
?>

<#7>
<?php

global $DIC;

$db = new CaT\Plugins\CourseMember\Settings\ilDB($ilDB);
$db->createPrimaryKey();
?>

<#8>
<?php

global $DIC;

$db = new CaT\Plugins\CourseMember\Members\ilDB($ilDB, $DIC->user());
$db->update1();
?>

<#9>
<?php

global $DIC;

$db = new CaT\Plugins\CourseMember\Settings\ilDB($ilDB);
$db->update1();
?>

<#10>
<?php

global $DIC;

$db = new CaT\Plugins\CourseMember\Members\ilDB($ilDB, $DIC->user());
$db->update2();
?>

<#11>
<?php

global $DIC;

$db = new CaT\Plugins\CourseMember\Members\ilDB($ilDB, $DIC->user());
$db->update3();
?>

<#12>
<?php

global $DIC;

$db = new CaT\Plugins\CourseMember\Settings\ilDB($ilDB);
$db->update2();
?>

<#13>
<?php

global $DIC;

$db = new CaT\Plugins\CourseMember\Settings\ilDB($ilDB);
$db->update3();
?>

<#14>
<?php

global $DIC;

$db = new CaT\Plugins\CourseMember\Settings\ilDB($ilDB);
$db->update4();
?>

<#15>
<?php

global $DIC;

$db = new CaT\Plugins\CourseMember\Members\ilDB($ilDB, $DIC->user());
$db->update4();
?>

<#16>
<?php

global $DIC;

$db = new CaT\Plugins\CourseMember\Members\ilDB($ilDB, $DIC->user());
$db->update5();
?>

<#17>
<?php

$plug = 'CourseMember';
$plug_id = 'xcmb';
$class_name = CaT\Plugins\CourseMember\UnboundProvider::class;

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
    $obj = new ilObjCourseMember();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#18>
<?php

$plug = 'CourseMember';
$plug_id = 'xcmb';
$class_name = CaT\Plugins\CourseMember\UnboundProvider::class;

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
    $obj = new ilObjCourseMember();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#19>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMember/classes/class.ilObjCourseMember.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);
$class_name = CaT\Plugins\CourseMember\SharedUnboundProvider::class;
$path = ILIAS_ABSOLUTE_PATH
    . "/Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMember/classes/SharedUnboundProvider.php";

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xcmb'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjCourseMember();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
    $provider = $provider_db->createSharedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#20>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMember/classes/class.ilObjCourseMember.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xcmb'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjCourseMember();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#21>
<?php

$db = new CaT\Plugins\CourseMember\LPOptions\ilDB($ilDB);
$db->update1();
?>

<#22>
<?php

$db = new CaT\Plugins\CourseMember\Settings\ilDB($ilDB);
$db->update5();
?>

<#23>
<?php

$db = new CaT\Plugins\CourseMember\Reminder\ilDB($ilDB);
$db->createTable();
?>

<#24>
<?php

$db = new CaT\Plugins\CourseMember\Reminder\ilDB($ilDB);
$db->createSequence();
?>

<#25>
<?php

$db = new CaT\Plugins\CourseMember\Reminder\ilDB($ilDB);
$db->createPrimaryKey();
?>

<#26>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseMember/classes/class.ilObjCourseMember.php");
global $DIC;
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($ilDB, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

$query = "SELECT object_data.obj_id FROM object_data WHERE type = 'xcmb'";
$result = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($result)) {
    $obj = new ilObjCourseMember();
    $obj->setId($row["obj_id"]);
    foreach ($provider_db->unboundProvidersOf($obj) as $provider) {
        $provider_db->update($provider);
    }
}
?>

<#27>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xcmb_siglstcfg')) {
    $db->createTable(
        'xcmb_siglstcfg',
        [
            'id' => [
                'type' => 'integer'
                ,'length' => 8
                ,'notnull' => true
            ],
            'name' => [
                'type' => 'text'
                ,'length' => 64
                ,'notnull' => true
            ],
            'description' => [
                'type' => 'text'
                ,'length' => 256
                ,'notnull' => false
            ],
            'is_default' => [
                'type' => 'integer'
                ,'length' => 1
                ,'notnull' => true
            ]
        ]
    );
    $db->createSequence('xcmb_siglstcfg');
}
?>

<#28>
<?php
global $DIC;
$db = $DIC['ilDB'];
try {
    $db->addPrimaryKey('xcmb_siglstcfg', ['id']);
} catch (\Exception $e) {
    $db->dropPrimaryKey('xcmb_siglstcfg');
    $db->addPrimaryKey('xcmb_siglstcfg', ['id']);
}
?>

<#29>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xcmb_siglstflds')) {
    $db->createTable(
        'xcmb_siglstflds',
        [
            'row_id' => [
                'type' => 'integer'
                ,'length' => 8
                ,'notnull' => true
            ],
            'id' => [
                'type' => 'integer'
                ,'length' => 8
                ,'notnull' => true
            ],
            'type' => [
                'type' => 'text'
                ,'length' => 64
                ,'notnull' => true
            ],
            'value' => [
                'type' => 'text'
                ,'length' => 256
                ,'notnull' => false
            ],
        ]
    );
    $db->createSequence('xcmb_siglstflds');
}
?>

<#30>
<?php
global $DIC;
$db = $DIC['ilDB'];
try {
    $db->addPrimaryKey('xcmb_siglstflds', ['row_id']);
} catch (\Exception $e) {
    $db->dropPrimaryKey('xcmb_siglstflds');
    $db->addPrimaryKey('xcmb_siglstflds', ['row_id']);
}
?>

<#31>
<?php
global $DIC;
$db = $DIC['ilDB'];
try {
    $db->addIndex('xcmb_siglstflds', ['id'], 'fid');
} catch (\Exception $e) {
    $db->dropIndex('xcmb_siglstflds', 'fid');
    $db->addIndex('xcmb_siglstflds', ['id'], 'fid');
    ;
}
?>

<#32>
<?php
global $DIC;
$db = $DIC['ilDB'];
try {
    $db->addIndex('xcmb_siglstcfg', ['name'], 'fnm');
} catch (\Exception $e) {
    $db->dropIndex('xcmb_siglstflds', 'fnm');
    $db->addIndex('xcmb_siglstflds', ['name'], 'fnm');
    ;
}
?>

<#33>
<?php

global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xcmb_siglstcrs')) {
    $db->createTable(
        'xcmb_siglstcrs',
        [
            'id' => [
                'type' => 'integer'
                ,'length' => 8
                ,'notnull' => true
            ],
            'crs_id' => [
                'type' => 'integer'
                ,'length' => 8
                ,'notnull' => true
            ]
        ]
    );
}
?>

<#34>
<?php
global $DIC;
$db = $DIC['ilDB'];
try {
    $db->addPrimaryKey('xcmb_siglstcrs', ['id','crs_id']);
} catch (\Exception $e) {
    $db->dropPrimaryKey('xcmb_siglstcrs');
    $db->addPrimaryKey('xcmb_siglstcrs', ['id','crs_id']);
}
?>

<#35>
<?php
global $DIC;
/** @var ilDBInterface $db */
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('xcmb_siglstcfg', 'mail_template_id')) {
    $field = [
        'type' => 'text'
        ,'length' => 64
        ,'notnull' => false
    ];
    $db->addTableColumn('xcmb_siglstcfg', 'mail_template_id', $field);
}
?>
