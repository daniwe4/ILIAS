<#1>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xtdr_settings')) {
    $fields = [
        'is_online' => [
                'type' => 'integer',
                'length' => 1,
                'notnull' => true],
        'id' => [
                'type' => 'integer',
                'length' => 4,
                'notnull' => true]
        ];
    $db->createTable('xtdr_settings', $fields);
}
?>

<#2>
<?php
$ilDB->addPrimaryKey('xtdr_settings', ['id']);
?>

<#3>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('xtdr_settings', 'is_global')) {
    $db->addTableColumn(
        'xtdr_settings',
        'is_global',
        [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true
        ]
    );
}
?>

<#4>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xtdr_local_roles')) {
    $fields = [
        'id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'obj_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'local_role' => [
            'type' => 'text',
            'length' => 255,
            'notnull' => true
        ]
    ];
    $db->createTable('xtdr_local_roles', $fields);
}
?>

<#5>
<?php
try {
    $this->db->addPrimaryKey("xtdr_local_roles", ["id"]);
} catch (\PDOException $e) {
    $this->db->dropPrimaryKey("xtdr_local_roles");
    $this->db->addPrimaryKey("xtdr_local_roles", ["id"]);
}
?>

<#6>
<?php
if (!$this->db->sequenceExists("xtdr_local_roles")) {
    $this->db->createSequence("xtdr_local_roles");
}
?>