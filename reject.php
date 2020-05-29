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

$DIC->globalScreen()->tool()->context()->claim()->external();
$lng->loadLanguageModule("tms");
$local_tpl = new ilGlobalTemplate("tpl.main.html", true, true);
$local_tpl->addBlockFile("CONTENT", "content", "tpl.reject.html");
$local_tpl->setVariable("HEADER_ICON", ilUtil::getImagePath("HeaderIcon.svg"));
$local_tpl->setVariable("MESSAGE_HEADING", $lng->txt('crs_reject'));
$show_error = true;
if ($show_error) {
    $local_tpl->setVariable("MESSAGE_TYPE", 'alert-danger');
    $local_tpl->setVariable("MESSAGE", $lng->txt('no_reject_possible'));
    $local_tpl->setVariable("LINK", 'login.php');
    $local_tpl->setVariable("TXT_LINK", $lng->txt('to_login'));

    $tpl->setContent($local_tpl->get());
    $tpl->printToStdout();
} else {
    $local_tpl->setVariable("MESSAGE_TYPE", 'alert-success');
    $local_tpl->setVariable("MESSAGE", $lng->txt('successfully_decline'));
    $local_tpl->setVariable("LINK", 'login.php');
    $local_tpl->setVariable("TXT_LINK", $lng->txt('to_login'));

    $tpl->setContent($local_tpl->get());
    $tpl->printToStdout();
}
