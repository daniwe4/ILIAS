<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types=1);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilContext::init(ilContext::CONTEXT_TMS_PRINT);
ilInitialisation::initILIAS();

global $DIC;
$lng = $DIC["lng"];
$lng->loadLanguageModule("tms");
$get = $_GET;
$show_error = false;
$err_msg = '';

if (!array_key_exists('file', $get)) {
    $show_error = true;
}

if (!ilPluginAdmin::isPluginActive('xcml')) {
    $show_error = true;
}

if (!$show_error) {
    /** @var ilCourseMailingPlugin $xcmb */
    $pl_xcml = ilPluginAdmin::getPluginObjectById('xcml');
    $file = trim($get['file']);

    try {
        $pl_xcml->rejectUserByHash($file);
    } catch (\Exception $e) {
        $show_error = true;
    }
}

if ($show_error) {
    $tpl = $DIC["tpl"];
    $lng = $DIC["lng"];
    $tpl->addBlockFile("CONTENT", "content", "tpl.error.html");
    $lng->loadLanguageModule("error");

    $tpl->setVariable("HEADER_ICON", ilUtil::getImagePath("HeaderIcon.svg"));

    $tpl->setCurrentBlock("content");
    $tpl->setVariable("ERROR_MESSAGE", $lng->txt('no_reject_possible'));
    $tpl->setVariable("MESSAGE_HEADING", $lng->txt('error_sry_error'));

    $tpl->show();
}
