<#1>
<?php
global $DIC;
$db = $DIC['ilDB'];

if (!$db->tableExists('x_obmd_config')) {
    $columns =
        [
            'id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
            'title' => ['type' => 'text', 'length' => 128, 'notnull' => true],
            'position' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
            'description' => ['type' => 'text', 'length' => 1024, 'notnull' => false]
        ];
    $db->createTable('x_obmd_config', $columns);
    $db->createSequence('x_obmd_config');
}
?>

<#2>
<?php
global $DIC;
$db = $DIC['ilDB'];

$db->addPrimaryKey('x_obmd_config', ['id']);
?>

<#3>
<?php
global $DIC;
$db = $DIC['ilDB'];

if (!$db->tableExists('x_obmd_conf_orgu')) {
    $columns =
        [
            'id' => ['type' => 'integer', 'length' => 4, 'notnull' => true],
            'orgu' => ['type' => 'integer', 'length' => 4, 'notnull' => true]
        ];
    $db->createTable('x_obmd_conf_orgu', $columns);
}
?>

<#4>
<?php
global $DIC;
$db = $DIC['ilDB'];

$db->addPrimaryKey('x_obmd_conf_orgu', ['id','orgu']);
?>


<#5>
<?php
global $DIC;
$db = $DIC['ilDB'];

$db->manipulate('ALTER TABLE x_obmd_config ADD UNIQUE (title)');
?>