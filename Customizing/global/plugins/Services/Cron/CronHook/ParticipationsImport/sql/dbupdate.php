<#1>
<?php
global $DIC;
$db = $DIC['ilDB'];

if (!$db->tableExists('part_imp_bk_ps_maps')) {
    $columns =
        [
            'key' => ['type' => 'text', 'length' => 64, 'notnull' => true],
            'val' => ['type' => 'text', 'length' => 32, 'notnull' => true],
            'type' => ['type' => 'text', 'length' => 24, 'notnull' => true]
        ];
    $db->createTable('part_imp_bk_ps_maps', $columns);
}
?>

<#2>
<?php
global $DIC;
$db = $DIC['ilDB'];
try {
    $db->addPrimaryKey('part_imp_bk_ps_maps', ['key','val','type']);
} catch (\Exception $e) {
    $db->dropPrimaryKey('part_imp_bk_ps_maps');
    $db->addPrimaryKey('part_imp_bk_ps_maps', ['key','val','type']);
}
?>