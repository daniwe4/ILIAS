<#1>
<?php
    global $DIC;
    $db = $DIC['ilDB'];
if (!$db->tableExists('xebo_settings')) {
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
    $db->createTable('xebo_settings', $fields);
}
?>

<#2>
<?php
    global $DIC;
    $db = $DIC['ilDB'];
if ($db->tableColumnExists('xebo_settings', 'obj_id')) {
    $db->addPrimaryKey('xebo_settings', ['obj_id']);
}
?>

<#3>
<?php
if (!$this->db->tableColumnExists('xebo_settings', "invisible_crs_topics")) {
    $field = [
        "type" => "clob",
        "default" => null,
        "notnull" => false
    ];
    $this->db->addTableColumn('xebo_settings', "invisible_crs_topics", $field);
}
?>