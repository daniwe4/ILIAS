<#1>
<?php

global $DIC;
$db = new CaT\Plugins\WBDCommunicator\Config\UDF\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$db->createTable();
?>
<#2>
<?php

global $DIC;
$db = new CaT\Plugins\WBDCommunicator\Config\UDF\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$db->createPrimaryKey();
?>

<#3>
<?php

global $DIC;
$db = new CaT\Plugins\WBDCommunicator\Config\Connection\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$db->createTable();
?>
<#4>
<?php

global $DIC;
$db = new CaT\Plugins\WBDCommunicator\Config\Connection\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$db->createPrimaryKey();
?>
<#5>
<?php

global $DIC;
$db = new CaT\Plugins\WBDCommunicator\Config\Connection\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$db->createPrimaryKey();
?>

<#6>
<?php
global $DIC;
$db = new CaT\WBD\ErrorLog\ilDB($DIC["ilDB"]);
$db->createTable();
?>
<#7>
<?php
global $DIC;
$db = new CaT\WBD\ErrorLog\ilDB($DIC["ilDB"]);
$db->createSequence();
?>
<#8>
<?php
global $DIC;
$db = new CaT\WBD\ErrorLog\ilDB($DIC["ilDB"]);
$db->addPrimaryKey();
?>

<#9>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xwbd_imported_courses')) {
    $columns = [
        'crs_id' =>
            ['type' => 'integer','length' => 4,'notnull' => true],
        'begin_date' =>
            ['type' => 'date','notnull' => false],
        'end_date' =>
            ['type' => 'date','notnull' => false],
        'idd_learning_time' =>
            ['type' => 'integer','length' => 4,'notnull' => true],
        'wbd_learning_type' =>
            ['type' => 'text','length' => 32,'notnull' => true],
        'wbd_learning_content' =>
            ['type' => 'text','length' => 32,'notnull' => true],
        'internal_id' =>
            ['type' => 'text','length' => 256,'notnull' => true],
        'title' =>
            ['type' => 'text','length' => 516,'notnull' => true],
    ];
    $db->createTable('xwbd_imported_courses', $columns);
}
?>
<#10>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xwbd_imported_courses_seq')) {
    $db->createSequence('xwbd_imported_courses');
}
?>
<#11>
<?php
global $DIC;
$db = $DIC['ilDB'];
try {
    $db->addPrimaryKey('xwbd_imported_courses', ['crs_id']);
} catch (\Exception $e) {
    $db->dropPrimaryKey('xwbd_imported_courses');
    $db->addPrimaryKey('xwbd_imported_courses', ['crs_id']);
}
?>

<#12>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xwbd_announced_cases')) {
    $columns = [
        'crs_id' =>
            ['type' => 'integer','length' => 4,'notnull' => true],
        'usr_id' =>
            ['type' => 'integer','length' => 4,'notnull' => true],
    ];
    $db->createTable('xwbd_announced_cases', $columns);
}
?>
<#13>
<?php
global $DIC;
$db = $DIC['ilDB'];
try {
    $db->addPrimaryKey('xwbd_announced_cases', ['crs_id','usr_id']);
} catch (\Exception $e) {
    $db->dropPrimaryKey('xwbd_announced_cases');
    $db->addPrimaryKey('xwbd_announced_cases', ['crs_id','usr_id']);
}
?>

<#14>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xwbd_book_status')) {
    $columns = [
        'crs_id' =>
            ['type' => 'integer','length' => 4,'notnull' => true],
        'usr_id' =>
            ['type' => 'integer','length' => 4,'notnull' => true],
        'wbd_booking_status' =>
            ['type' => 'text','length' => 128,'notnull' => false],
        'wbd_booking_id' =>
            ['type' => 'text','length' => 128,'notnull' => false]
    ];
    $db->createTable('xwbd_book_status', $columns);
}
?>
<#15>
<?php
global $DIC;
$db = $DIC['ilDB'];
try {
    $db->addPrimaryKey('xwbd_book_status', ['crs_id','usr_id']);
} catch (\Exception $e) {
    $db->dropPrimaryKey('xwbd_book_status');
    $db->addPrimaryKey('xwbd_book_status', ['crs_id','usr_id']);
}
?>

<#16>
<?php

global $DIC;
$db = new CaT\Plugins\WBDCommunicator\Config\Tgic\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$db->createTable();
?>
<#17>
<?php

global $DIC;
$db = new CaT\Plugins\WBDCommunicator\Config\Tgic\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$db->createPrimaryKey();
?>
<#18>
<?php

global $DIC;
$db = new CaT\Plugins\WBDCommunicator\Config\Tgic\ilDB($DIC["ilDB"], $DIC["ilUser"]);
$db->createSequence();
?>

<#19>
<?php
;
?>

<#20>
<?php
;
?>

<#21>
<?php
global $DIC;
$db = $DIC["ilDB"];
if($db->tableColumnExists("wbd_request_errors", "login")) {
    $db->dropTableColumn("wbd_request_errors", "login");
}
?>

<#22>
<?php
global $DIC;
$db = $DIC["ilDB"];
if($db->tableColumnExists("wbd_request_errors", "firstname")) {
    $db->dropTableColumn("wbd_request_errors", "firstname");
}
?>

<#23>
<?php
global $DIC;
$db = $DIC["ilDB"];
if($db->tableColumnExists("wbd_request_errors", "lastname")) {
    $db->dropTableColumn("wbd_request_errors", "lastname");
}
?>

<#24>
<?php
global $DIC;
$db = $DIC["ilDB"];
if($db->tableColumnExists("xwbd_imported_courses", "wbd_learning_type")) {
    $db->modifyTableColumn(
        "xwbd_imported_courses",
        'wbd_learning_type',
        ['type' => 'text','length' => 32,'notnull' => false]
    );
}
?>

<#25>
<?php
global $DIC;
$db = $DIC["ilDB"];
if($db->tableColumnExists("xwbd_imported_courses", "wbd_learning_content")) {
    $db->modifyTableColumn(
        "xwbd_imported_courses",
        'wbd_learning_content',
        ['type' => 'text','length' => 32,'notnull' => false]
    );
}
?>