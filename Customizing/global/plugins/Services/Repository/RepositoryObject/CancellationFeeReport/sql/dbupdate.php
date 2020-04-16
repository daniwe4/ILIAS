<#1>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xcfr_settings')) {
    $fields = [
        'is_online' => [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true
        ],
        'id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'is_global' => [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true
        ]
        ];
    $db->createTable('xcfr_settings', $fields);
}
?>

<#2>
<?php
try {
    $ilDB->addPrimaryKey('xcfr_settings', ['id']);
} catch (\Exception $e) {
    $ilDB->dropPrimaryKey('xcfr_settings');
    $ilDB->addPrimaryKey('xcfr_settings', ['id']);
}
?>
