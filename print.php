<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types=1);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilContext::init(ilContext::CONTEXT_TMS_PRINT);
ilInitialisation::initILIAS();

global $DIC;
$ctrl = $DIC["ilCtrl"];
$lng = $DIC["lng"];
$lng->loadLanguageModule("tms");
$get = $_GET;

if (!ilPluginAdmin::isPluginActive('docdeliver')) {
    ilUtil::sendInfo($lng->txt('no_document_available'), true);
    $ctrl->redirectToUrl(ILIAS_HTTP_PATH . "/goto.php?target=root_1&client_id=" . CLIENT_ID);
}

if (!ilPluginAdmin::isPluginActive('xcmb')) {
    ilUtil::sendInfo($lng->txt('no_document_available'), true);
    $ctrl->redirectToUrl(ILIAS_HTTP_PATH . "/goto.php?target=root_1&client_id=" . CLIENT_ID);
}

if (
    !array_key_exists('file', $get) &&
    (is_null($get['file']) || trim($get['file']) == '')
) {
    ilUtil::sendInfo($lng->txt('no_document_available'), true);
    $ctrl->redirectToUrl(ILIAS_HTTP_PATH . "/goto.php?target=root_1&client_id=" . CLIENT_ID);
}

/** @var ilDocumentDeliveryPlugin $xcmb */
$doc_deliver = ilPluginAdmin::getPluginObjectById('docdeliver');
$file = trim($get['file']);

$document = null;
try {
    $document = $doc_deliver->printDocumentForHash($file);
} catch (\Exception $e) {
    $DIC->globalScreen()->tool()->context()->claim()->external();
    $lng->loadLanguageModule("error");
    $lng->loadLanguageModule("tms");

    $local_tpl = new ilGlobalTemplate("tpl.main.html", true, true);
    $local_tpl->addBlockFile("CONTENT", "content", "tpl.print.html");
    $local_tpl->setVariable("MESSAGE_TYPE", 'alert-danger');
    $local_tpl->setVariable("HEADER_ICON", ilUtil::getImagePath("HeaderIcon.svg"));
    $local_tpl->setVariable("MESSAGE_HEADING", $lng->txt('error_sry_error'));
    $local_tpl->setVariable("ERROR_MESSAGE", $lng->txt('no_document_available'));
    $local_tpl->setVariable("LINK", 'login.php');
    $local_tpl->setVariable("TXT_LINK", $lng->txt('to_login'));

    $tpl->setContent($local_tpl->get());
    $tpl->printToStdout();
}
