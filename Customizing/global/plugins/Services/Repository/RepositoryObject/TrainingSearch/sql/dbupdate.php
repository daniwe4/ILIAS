<#1>
<?php

global $DIC;
$settings_db = new \CaT\Plugins\TrainingSearch\Settings\ilDB($DIC["ilDB"]);
$settings_db->createTable();
?>

<#2>
<?php

global $DIC;
$settings_db = new \CaT\Plugins\TrainingSearch\Settings\ilDB($DIC["ilDB"]);
$settings_db->addPrimaryKey();
?>

<#3>
<?php
ilOrgUnitOperationContextQueries::registerNewContext(
    "xtrs",
    ilOrgUnitOperationContext::CONTEXT_OBJECT
);
?>

<#4>
<?php
ilOrgUnitOperationQueries::registerNewOperation(
    "orgu_book_user",
    "Book authorized members",
    "xtrs"
);
?>

<#5>
<?php

global $DIC;
$settings_db = new \CaT\Plugins\TrainingSearch\Settings\ilDB($DIC["ilDB"]);
$settings_db->update1();
?>

<#6>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xtrs_settings_topics')) {
    $fields = [
        'row_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'obj_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'val_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ]
    ];
    $db->createTable('xtrs_settings_topics', $fields);
}
?>

<#7>
<?php
global $DIC;
$db = $DIC['ilDB'];
try {
    $db->addPrimaryKey('xtrs_settings_topics', ['row_id']);
} catch (\PDOException $e) {
    $db->dropPrimaryKey('xtrs_settings_topics');
    $db->addPrimaryKey('xtrs_settings_topics', ['row_id']);
}
?>

<#8>
<?php
global $DIC;
$db = $DIC['ilDB'];
if ($db->tableExists('xtrs_settings_topics')) {
    $db->createSequence('xtrs_settings_topics');
} else {
    throw new \Exception('table xtrs_settings_topics missing');
}
?>

<#9>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xtrs_settings_cats')) {
    $fields = [
        'row_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'obj_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'val_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ]
    ];
    $db->createTable('xtrs_settings_cats', $fields);
}
?>

<#10>
<?php
global $DIC;
$db = $DIC['ilDB'];
try {
    $db->addPrimaryKey('xtrs_settings_cats', ['row_id']);
} catch (\PDOException $e) {
    $db->dropPrimaryKey('xtrs_settings_cats');
    $db->addPrimaryKey('xtrs_settings_cats', ['row_id']);
}
?>

<#11>
<?php
global $DIC;
$db = $DIC['ilDB'];
if ($db->tableExists('xtrs_settings_cats')) {
    $db->createSequence('xtrs_settings_cats');
} else {
    throw new \Exception('table xtrs_settings_cats missing');
}
?>

<#12>
<?php
global $DIC;
$db = $DIC['ilDB'];
if (!$db->tableExists('xtrs_settings_t_g')) {
    $fields = [
        'row_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'obj_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ],
        'val_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true
        ]
    ];
    $db->createTable('xtrs_settings_t_g', $fields);
}
?>

<#13>
<?php
global $DIC;
$db = $DIC['ilDB'];
if ($db->tableExists('xtrs_settings_t_g')) {
    $db->createSequence('xtrs_settings_t_g');
} else {
    throw new \Exception('table xtrs_settings_t_g missing');
}
?>

<#14>
<?php

global $DIC;
$settings_db = new \CaT\Plugins\TrainingSearch\Settings\ilDB($DIC["ilDB"]);
$settings_db->update2();
?>