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
    $tpl = $DIC["tpl"];
    $lng = $DIC["lng"];
    $tpl->addBlockFile("CONTENT", "content", "tpl.error.html");
    $lng->loadLanguageModule("error");
    $tpl->setVariable("HEADER_ICON", ilUtil::getImagePath("HeaderIcon.svg"));
    $tpl->setCurrentBlock("content");
    $tpl->setVariable("ERROR_MESSAGE", $lng->txt('no_document_available'));
    $tpl->setVariable("MESSAGE_HEADING", $lng->txt('error_sry_error'));

    $tpl->show();
}
