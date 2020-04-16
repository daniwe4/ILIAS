<#1>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xtda_settings')) {
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
    $db->createTable('xtda_settings', $fields);
}
?>

<#2>
<?php
$ilDB->addPrimaryKey('xtda_settings', ['id']);
?>

<#3>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableColumnExists('xtda_settings', 'is_global')) {
    $db->addTableColumn(
        'xtda_settings',
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
if (!$db->tableExists('xtda_local_roles')) {
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
    $db->createTable('xtda_local_roles', $fields);
}
?>

<#5>
<?php
try {
    $this->db->addPrimaryKey("xtda_local_roles", ["id"]);
} catch (\PDOException $e) {
    $this->db->dropPrimaryKey("xtda_local_roles");
    $this->db->addPrimaryKey("xtda_local_roles", ["id"]);
}
?>

<#6>
<?php
if (!$this->db->sequenceExists("xtda_local_roles")) {
    $this->db->createSequence("xtda_local_roles");
}
?>