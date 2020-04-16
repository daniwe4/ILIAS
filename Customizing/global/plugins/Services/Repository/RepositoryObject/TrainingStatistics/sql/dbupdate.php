<#1>
<?php
    global $DIC;
    $db = $DIC['ilDB'];
if (!$db->tableExists('xrts_settings')) {
    $fields = [
    'obj_id' => ['type' => 'integer'
                ,'length' => 4
                ,'notnull' => true]
    ,'aggregate_id' => ['type' => 'text'
                ,'length' => 64
                ,'notnull' => true]
    ,'online' => ['type' => 'integer'
                ,'length' => 1
                ,'notnull' => true]
    ,'global' => ['type' => 'integer'
                ,'length' => 1
                ,'notnull' => true]
    ];
    $db->createTable('xrts_settings', $fields);
}
?>

<#2>
<?php
    global $DIC;
    $db = $DIC['ilDB'];
if ($db->tableColumnExists('xrts_settings', 'obj_id')) {
    $db->addPrimaryKey('xrts_settings', ['obj_id']);
}
?>