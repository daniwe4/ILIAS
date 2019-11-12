<?php

/* Copyright (c) 2019 - Stefan Hecken <stefan.hecken@concepts-and-training.de> - Extended GPL, see LICENSE */

declare(strict_types=1);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilContext::init(ilContext::CONTEXT_TMS_PRINT);
ilInitialisation::initILIAS();

global $DIC;
$ctrl = $DIC["ilCtrl"];
$lng = $DIC["lng"];
$get = $_GET;

if (!ilPluginAdmin::isPluginActive('docdeliver')) {
    ilUtil::sendInfo($lng->txt('no_list_available'), true);
    $ctrl->redirectToUrl("http://localhost/cat_ilias/tms54/goto.php?target=root_1&client_id=tms54");
}

if (!ilPluginAdmin::isPluginActive('xcmb')) {
    ilUtil::sendInfo($lng->txt('no_list_available'), true);
    $ctrl->redirectToUrl("http://localhost/cat_ilias/tms54/goto.php?target=root_1&client_id=tms54");
}

if (
    !array_key_exists('file', $get) &&
    (is_null($get['file']) || trim($get['file']) == '')
) {
    ilUtil::sendInfo($lng->txt('no_list_available'), true);
    $ctrl->redirectToUrl("http://localhost/cat_ilias/tms54/goto.php?target=root_1&client_id=tms54");
}

/** @var ilDocumentDeliveryPlugin $xcmb */
$doc_deliver = ilPluginAdmin::getPluginObjectById('docdeliver');
$file = trim($get['file']);

$document = null;
try {
    $document = $doc_deliver->printDocumentForHash($file);
} catch (\LogicException $e) {
    ilUtil::sendInfo($lng->txt('no_list_available'), true);
    $ctrl->redirectToUrl("http://localhost/cat_ilias/tms54/goto.php?target=root_1&client_id=tms54");
}
