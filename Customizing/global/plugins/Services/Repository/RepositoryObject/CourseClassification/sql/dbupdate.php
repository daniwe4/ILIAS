<#1>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$category_db = new CaT\Plugins\CourseClassification\Options\Category\ilDB($ilDB);
$eduprogramme_db = new CaT\Plugins\CourseClassification\Options\Eduprogram\ilDB($ilDB);
$media_db = new CaT\Plugins\CourseClassification\Options\Media\ilDB($ilDB);
$method_db = new CaT\Plugins\CourseClassification\Options\Method\ilDB($ilDB);
$targetgroup_db = new CaT\Plugins\CourseClassification\Options\TargetGroup\ilDB($ilDB);
$topic_db = new CaT\Plugins\CourseClassification\Options\Topic\ilDB($ilDB);
$type_db = new CaT\Plugins\CourseClassification\Options\Type\ilDB($ilDB);

$category_db->install();
$eduprogramme_db->install();
$media_db->install();
$method_db->install();
$targetgroup_db->install();
$topic_db->install();
$type_db->install();
?>

<#2>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$category_db = new CaT\Plugins\CourseClassification\Options\Category\ilDB($ilDB);
$category_db->configurePrimaryKeys();
?>

<#3>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$eduprogramme_db = new CaT\Plugins\CourseClassification\Options\Eduprogram\ilDB($ilDB);
$eduprogramme_db->configurePrimaryKeys();
?>

<#4>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$media_db = new CaT\Plugins\CourseClassification\Options\Media\ilDB($ilDB);
$media_db->configurePrimaryKeys();
?>

<#5>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$method_db = new CaT\Plugins\CourseClassification\Options\Method\ilDB($ilDB);
$method_db->configurePrimaryKeys();
?>

<#6>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$targetgroup_db = new CaT\Plugins\CourseClassification\Options\TargetGroup\ilDB($ilDB);
$targetgroup_db->configurePrimaryKeys();
?>

<#7>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$topic_db = new CaT\Plugins\CourseClassification\Options\Topic\ilDB($ilDB);
$topic_db->configurePrimaryKeys();
?>

<#8>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$type_db = new CaT\Plugins\CourseClassification\Options\Type\ilDB($ilDB);
$type_db->configurePrimaryKeys();
?>

<#9>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$category_db = new CaT\Plugins\CourseClassification\Options\Category\ilDB($ilDB);
$category_db->createSequence();
?>

<#10>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$eduprogramme_db = new CaT\Plugins\CourseClassification\Options\Eduprogram\ilDB($ilDB);
$eduprogramme_db->createSequence();
?>

<#11>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$media_db = new CaT\Plugins\CourseClassification\Options\Media\ilDB($ilDB);
$media_db->createSequence();
?>

<#12>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$method_db = new CaT\Plugins\CourseClassification\Options\Method\ilDB($ilDB);
$method_db->createSequence();
?>

<#13>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$targetgroup_db = new CaT\Plugins\CourseClassification\Options\TargetGroup\ilDB($ilDB);
$targetgroup_db->createSequence();
?>

<#14>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$topic_db = new CaT\Plugins\CourseClassification\Options\Topic\ilDB($ilDB);
$topic_db->createSequence();
?>

<#15>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$type_db = new CaT\Plugins\CourseClassification\Options\Type\ilDB($ilDB);
$type_db->createSequence();
?>

<#16>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$settings_db = new CaT\Plugins\CourseClassification\Settings\ilDB($ilDB);

$settings_db->createTables();
?>

<#17>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$settings_db = new CaT\Plugins\CourseClassification\Settings\ilDB($ilDB);

$settings_db->createPrimaryKeys();
?>

<#18>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$settings_db = new CaT\Plugins\CourseClassification\Settings\ilDB($ilDB);

$settings_db->update1();
?>

<#19>
<?php
    $ilDB->renameTable("xccl_category", "xccl_tmp");
    $ilDB->renameTable("xccl_topic", "xccl_category");
    $ilDB->renameTable("xccl_tmp", "xccl_topic");
?>

<#20>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$settings_db = new CaT\Plugins\CourseClassification\Settings\ilDB($ilDB);

$settings_db->update2();
?>

<#21>
<?php

global $DIC;
$db = $DIC->database();
$provider_db = new CaT\Ente\ILIAS\ilProviderDB($db, $DIC->repositoryTree(), $DIC["ilObjDataCache"]);

require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/classes/class.ilObjCourseClassification.php");
$query = $db->query("SELECT obj_id FROM object_data WHERE type = 'xccl'");
while ($res = $db->fetchAssoc($query)) {
    $obj = new ilObjCourseClassification();
    $obj->setId($res["obj_id"]);
    $provider_db->createSeparatedUnboundProvider($obj, "crs", CaT\Plugins\CourseClassification\UnboundProvider::class, "Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/classes/UnboundProvider.php");
}

?>

<#22>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$settings_db = new CaT\Plugins\CourseClassification\Settings\ilDB($ilDB);
$settings_db->update3();
?>

<#23>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$settings_db = new CaT\Plugins\CourseClassification\Settings\ilDB($ilDB);
$settings_db->update4();
?>

<#24>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$settings_db = new CaT\Plugins\CourseClassification\Settings\ilDB($ilDB);
$settings_db->update5();
?>

<#25>
<?php
;
?>

<#26>
<?php
;
?>

<#27>
<?php
;
?>

<#28>
<?php

$plug = 'CourseClassification';
$plug_id = 'xccl';
$class_name = CaT\Plugins\CourseClassification\UnboundProvider::class;

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
    $obj = new ilObjCourseClassification();
    $obj->setId($row["obj_id"]);
    $path = ILIAS_ABSOLUTE_PATH
        . "/Customizing/global/plugins/Services/Repository/RepositoryObject/$plug/classes/UnboundProvider.php";

    $provider = $provider_db->createSeparatedUnboundProvider($obj, "crs", $class_name, $path);
    $provider_db->update($provider);
}
?>

<#29>
<?php
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/vendor/autoload.php");
$settings_db = new CaT\Plugins\CourseClassification\AdditionalLinks\ilDB($ilDB);
$settings_db->install();
?>

<#30>
<?php
global $DIC;
$db = $DIC['ilDB'];
if ($db->tableColumnExists('hhd_crs_topics', 'list_data')) {
    $field = [
        'type' => 'text',
        'length' => 128,
        'notnull' => true
    ];

    $db->modifyTableColumn('hhd_crs_topics', 'list_data', $field);
}
?>

<#31>
<?php
global $DIC;
$db = $DIC['ilDB'];

$q = "SELECT SUBSTRING(caption, 1, 50) AS short_caption, caption, COUNT(caption) AS count_caption FROM xccl_topic WHERE LENGTH(caption) > 50 GROUP BY SUBSTRING(caption, 1, 50)";
$res = $db->query($q);
while ($row = $db->fetchAssoc($res)) {
    if ($row['count_caption'] > 1) {
        die("Ein Titel der Themen kommt in gekürzter Form mehrfach vor." . $row['short_caption']);
    }

    $q2 = "UPDATE hhd_crs_topics SET list_data = '" . $row['caption'] . "' WHERE list_data = '" . $row['short_caption'] . "'";
    $db->manipulate($q2);
}
?>

<#32>
<?php
global $DIC;
$db = $DIC['ilDB'];
if ($db->tableColumnExists('hhd_crs_categories', 'list_data')) {
    $field = [
        'type' => 'text',
        'length' => 128,
        'notnull' => true
    ];

    $db->modifyTableColumn('hhd_crs_categories', 'list_data', $field);
}
?>

<#33>
<?php
global $DIC;
$db = $DIC['ilDB'];

$q = "SELECT SUBSTRING(caption, 1, 50) AS short_caption, caption, COUNT(caption) AS count_caption FROM xccl_category WHERE LENGTH(caption) > 50 GROUP BY SUBSTRING(caption, 1, 50)";
$res = $db->query($q);
while ($row = $db->fetchAssoc($res)) {
    if ($row['count_caption'] > 1) {
        die("Ein Titel der Kategorie kommt in gekürzter Form mehrfach vor." . $row['short_caption']);
    }

    $q2 = "UPDATE hhd_crs_categories SET list_data = '" . $row['caption'] . "' WHERE list_data = '" . $row['short_caption'] . "'";
    $db->manipulate($q2);
}
?>

<#34>
<?php
global $DIC;
$db = $DIC['ilDB'];
if ($db->tableColumnExists('hhd_crs_target_groups', 'list_data')) {
    $field = [
        'type' => 'text',
        'length' => 128,
        'notnull' => true
    ];

    $db->modifyTableColumn('hhd_crs_target_groups', 'list_data', $field);
}
?>

<#35>
<?php
global $DIC;
$db = $DIC['ilDB'];

$q = "SELECT SUBSTRING(caption, 1, 50) AS short_caption, caption, COUNT(caption) AS count_caption FROM xccl_target_group WHERE LENGTH(caption) > 50 GROUP BY SUBSTRING(caption, 1, 50)";
$res = $db->query($q);
while ($row = $db->fetchAssoc($res)) {
    if ($row['count_caption'] > 1) {
        die("Ein Titel der Zielgruppen kommt in gekürzter Form mehrfach vor." . $row['short_caption']);
    }

    $q2 = "UPDATE hhd_crs_target_groups SET list_data = '" . $row['caption'] . "' WHERE list_data = '" . $row['short_caption'] . "'";
    $db->manipulate($q2);
}
?>

<#36>
<?php
global $DIC;
$db = $DIC['ilDB'];
if ($db->tableColumnExists('hst_crs_topics', 'list_data')) {
    $field = [
        'type' => 'text',
        'length' => 128,
        'notnull' => true
    ];

    $db->modifyTableColumn('hst_crs_topics', 'list_data', $field);
}
?>

<#37>
<?php
global $DIC;
$db = $DIC['ilDB'];
if ($db->tableColumnExists('hst_crs_categories', 'list_data')) {
    $field = [
        'type' => 'text',
        'length' => 128,
        'notnull' => true
    ];

    $db->modifyTableColumn('hst_crs_categories', 'list_data', $field);
}
?>

<#38>
<?php
global $DIC;
$db = $DIC['ilDB'];
if ($db->tableColumnExists('hst_crs_target_groups', 'list_data')) {
    $field = [
        'type' => 'text',
        'length' => 128,
        'notnull' => true
    ];

    $db->modifyTableColumn('hst_crs_target_groups', 'list_data', $field);
}
?>

<#39>
<?php
global $DIC;
$db = $DIC['ilDB'];
if ($db->tableColumnExists('hbuf_crs_categories', 'list_data')) {
    $field = [
        'type' => 'text',
        'length' => 128,
        'notnull' => true
    ];

    $db->modifyTableColumn('hbuf_crs_categories', 'list_data', $field);
}
?>

<#40>
<?php
global $DIC;
$db = $DIC['ilDB'];
if ($db->tableColumnExists('hbuf_crs_target_groups', 'list_data')) {
    $field = [
        'type' => 'text',
        'length' => 128,
        'notnull' => true
    ];

    $db->modifyTableColumn('hbuf_crs_target_groups', 'list_data', $field);
}
?>

<#41>
<?php
global $DIC;
$db = $DIC['ilDB'];

$q = "SELECT SUBSTRING(caption, 1, 50) AS short_caption, caption, COUNT(caption) AS count_caption FROM xccl_topic WHERE LENGTH(caption) > 50 GROUP BY SUBSTRING(caption, 1, 50)";
$res = $db->query($q);
while ($row = $db->fetchAssoc($res)) {
    if ($row['count_caption'] > 1) {
        die("Ein Titel der Themen kommt in gekürzter Form mehrfach vor." . $row['short_caption']);
    }

    $q2 = "UPDATE hhd_crs_topics SET list_data = '" . $row['caption'] . "' WHERE list_data = '" . $row['short_caption'] . "'";
    $db->manipulate($q2);
}
?>

<#42>
<?php
global $DIC;
$db = $DIC['ilDB'];

$q = "SELECT SUBSTRING(caption, 1, 50) AS short_caption, caption, COUNT(caption) AS count_caption FROM xccl_category WHERE LENGTH(caption) > 50 GROUP BY SUBSTRING(caption, 1, 50)";
$res = $db->query($q);
while ($row = $db->fetchAssoc($res)) {
    if ($row['count_caption'] > 1) {
        die("Ein Titel der Kategorie kommt in gekürzter Form mehrfach vor." . $row['short_caption']);
    }

    $q2 = "UPDATE hhd_crs_categories SET list_data = '" . $row['caption'] . "' WHERE list_data = '" . $row['short_caption'] . "'";
    $db->manipulate($q2);
}
?>

<#43>
<?php
global $DIC;
$db = $DIC['ilDB'];

$q = "SELECT SUBSTRING(caption, 1, 50) AS short_caption, caption, COUNT(caption) AS count_caption FROM xccl_target_group WHERE LENGTH(caption) > 50 GROUP BY SUBSTRING(caption, 1, 50)";
$res = $db->query($q);
while ($row = $db->fetchAssoc($res)) {
    if ($row['count_caption'] > 1) {
        die("Ein Titel der Zielgruppen kommt in gekürzter Form mehrfach vor." . $row['short_caption']);
    }

    $q2 = "UPDATE hhd_crs_target_groups SET list_data = '" . $row['caption'] . "' WHERE list_data = '" . $row['short_caption'] . "'";
    $db->manipulate($q2);
}
?>

<#44>
<?php
global $DIC;
$db = $DIC['ilDB'];
if ($db->tableColumnExists('hbuf_crs_topics', 'list_data')) {
    $field = [
        'type' => 'text',
        'length' => 128,
        'notnull' => true
    ];

    $db->modifyTableColumn('hbuf_crs_topics', 'list_data', $field);
}
?>
