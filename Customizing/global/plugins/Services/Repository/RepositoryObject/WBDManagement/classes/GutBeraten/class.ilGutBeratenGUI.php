<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> */

declare(strict_types=1);

use \ILIAS\UI\Factory;
use ILIAS\UI\Component\Button\Standard;
use ILIAS\UI\Component\Modal\RoundTrip;
use \ILIAS\UI\Renderer;
use \CaT\Plugins\WBDManagement\GutBeraten;
use \CaT\Plugins\WBDManagement\Settings\WBDManagement;

class ilGutBeratenGUI
{
    const CMD_SHOW = "showContent";
    const CMD_START_PROCESS = "start_process";
    const CMD_CANCEL = "cancel";
    const CMD_SAVE_ENTRIES = "saveEntries";
    const CMD_CONFIRM_ENTRIES = "getConfirmEntries";
    const CMD_BACK_TO_ENTER = "backToEnter";
    const CMD_DOWNLOAD_FILE = "downloadFile";

    const F_INFOS_FOR_NOW = "infos_for_now";
    const F_WBD_ID = "wbd_id";
    const F_WBD_AGREEMENT = "wbd_agreement";

    const ID_LENGTH = 18;

    const WBD_ID_REGEXP = "#^[0-9]{4}[0-9]{2}[0-9]{2}\-[0-9]{6}\-[0-9]{2}$#";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilAccess
     */
    protected $access;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var Factory
     */
    protected $ui_factory;

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var WBDManagement
     */
    protected $settings;

    /**
     * @var GutBeraten\DB
     */
    protected $gutberaten_db;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var string
     */
    protected $udf_field_status;

    /**
     * @var array
     */
    protected $user_data;

    /**
     * @var string
     */
    protected $plugin_dir;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilObjUser $user,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        Factory $ui_factory,
        Renderer $renderer,
        WBDManagement $settings,
        GutBeraten\DB $gutberaten_db,
        Closure $txt,
        int $obj_ref_id,
        string $cancel_link,
        string $plugin_dir
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->user = $user;
        $this->access = $access;
        $this->ui_factory = $ui_factory;
        $this->toolbar = $toolbar;
        $this->renderer = $renderer;
        $this->gutberaten_db = $gutberaten_db;
        $this->settings = $settings;
        $this->txt = $txt;
        $this->obj_ref_id = $obj_ref_id;
        $this->cancel_link = $cancel_link;
        $this->plugin_dir = $plugin_dir;

        $this->user_data = [];
    }

    /**y
     * @throws Exception
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_SHOW:
                if ($this->isAllowedToOrderTimeTransmission() && !$this->hasSaveData()) {
                    $this->showGutBeraten();
                } elseif ($this->isAllowedToOrderTimeTransmission() && $this->hasSaveData()) {
                    $this->showAlreadyOrdered();
                } else {
                    $this->showBase();
                }
                break;
            case self::CMD_CONFIRM_ENTRIES:
                if ($this->isAllowedToOrderTimeTransmission()) {
                    $this->showConfirmEntries();
                } else {
                    $this->showGutBeraten();
                }
                break;
            case self::CMD_SAVE_ENTRIES:
                $this->saveEntries();
                break;
            case self::CMD_BACK_TO_ENTER:
                if ($this->isAllowedToOrderTimeTransmission()) {
                    $this->showGutBeratenWithValues();
                } else {
                    $this->showBase();
                }
                break;
            case self::CMD_DOWNLOAD_FILE:
                $this->downloadFile();
                break;
            default:
                throw new Exception("ilGutBeratenGUI: Unknown command " . $cmd);
        }
    }

    protected function showGutBeraten(
        ilPropertyFormGUI $form = null,
        bool $trigger_click = false
    ) {
        if (is_null($form)) {
            $form = $this->initForm();
        }
        $modal = $this->getInputModal($form);
        $start_button = $this->getStartButton($modal);

        if ($trigger_click) {
            $start_button = $start_button->withAdditionalOnLoadCode(function ($id) {
                return "$('document').ready(function() {
					$('#{$id}').trigger('click');
				});";
            });
        }

        $this->showContent($start_button, $modal);
    }

    protected function showAlreadyOrdered()
    {
        $this->tpl->setContent($this->renderAlreadyOrdered());
    }

    protected function showBase()
    {
        $this->tpl->setContent($this->renderBase());
    }

    protected function showConfirmEntries()
    {
        $post = $_POST;
        $form = $this->initForm(false);

        $save = true;
        if (!$form->checkInput()) {
            $save = false;
        }

        if (!$this->checkCheckbox($post)) {
            $cb = $form->getItemByPostVar(self::F_WBD_AGREEMENT);
            $cb->setAlert($this->txt("pls_confirm"));
            $save = false;
        }

        if (!$save) {
            $form->setValuesByPost();
            $this->showGutBeraten($form, true);
            return;
        }

        if (isset($post[self::F_WBD_ID])) {
            ilSession::set(self::F_WBD_ID, $post[self::F_WBD_ID]);
        }

        if (isset($post[self::F_WBD_AGREEMENT])) {
            ilSession::set(self::F_WBD_AGREEMENT, $post[self::F_WBD_AGREEMENT]);
        }

        $modal = $this->getConfirmModal();

        $start_button = $this->getStartButton($modal)->withAdditionalOnLoadCode(function ($id) {
            return "$('document').ready(function() {
				$('#{$id}').trigger('click');
			});";
        });

        $this->showContent($start_button, $modal);
    }

    protected function showGutBeratenWithValues()
    {
        $form = $this->initForm(true);
        $modal = $this->getInputModal($form);
        $start_button = $this->getStartButton($modal)->withAdditionalOnLoadCode(function ($id) {
            return "$('document').ready(function() {
				$('#{$id}').trigger('click');
			});";
        });

        $this->showContent($start_button, $modal);
    }

    protected function showContent(Standard $start_button, Roundtrip $modal)
    {
        $this->setToolbar($start_button);

        $content = $this->renderBase();
        $content .= $this->renderer->render($modal);

        $this->tpl->setContent($content);
    }

    protected function setToolbar(Standard $start_button)
    {
        $this->toolbar->addComponent($start_button);
    }

    protected function saveEntries()
    {
        $this->saveSessionToUdf();

        ilUtil::sendSuccess($this->txt("save_entries"));
        $this->ctrl->redirectToURL($this->cancel_link);
    }

    protected function checkCheckbox(array $post) : bool
    {
        return array_key_exists(self::F_WBD_AGREEMENT, $post) &&
            $post[self::F_WBD_AGREEMENT] == 1;
    }

    protected function saveSessionToUdf()
    {
        $wbd_id = ilSession::get(self::F_WBD_ID);
        $this->gutberaten_db->saveWBDData(
            (int) $this->user->getId(),
            $wbd_id,
            \ilWBDManagementPlugin::TP_BILDUNGSDIENSTLEISTER
        );
        $this->user->update();
    }

    protected function downloadFile()
    {
        ilUtil::deliverFile(
            $this->settings->getDocumentPath(),
            basename($this->settings->getDocumentPath())
        );
    }

    protected function isAllowedToOrderTimeTransmission() : bool
    {
        return $this->access->checkAccess(
            "order_time_transmission",
            "",
            $this->obj_ref_id
        );
    }

    protected function hasSaveData() : bool
    {
        $data = $this->getData();

        if (is_null($data)) {
            return false;
        }

        return true;
    }

    /**
     * @return GutBeraten\WBDData | null
     */
    protected function getData()
    {
        if (is_null($this->user_data[$this->user->getId()])) {
            $this->user_data[
                $this->user->getId()
            ] = $this->gutberaten_db->selectFor(
                (int) $this->user->getId()
            );
        }
        return $this->user_data[$this->user->getId()];
    }

    protected function renderAlreadyOrdered() : string
    {
        $data = $this->getData();
        $wbd_id = $data->getWbdId();
        $status = $data->getStatus();
        $email = html_entity_decode($this->settings->getEmail());

        $tpl = new \ilTemplate("tpl.wbd_info.html", true, true, $this->plugin_dir);
        $tpl->setVariable("WBD_INFO_HEADER", $this->txt("infos"));

        $tpl->setVariable("WBD_ID_TITLE", $this->txt("wbd_id"));
        $tpl->setVariable("WBD_ID_VALUE", $wbd_id);

        $tpl->setVariable("WBD_STATUS_TITLE", $this->txt("ordered_as"));
        $tpl->setVariable("WBD_STATUS_VALUE", $status);

        $tpl->setVariable("WBD_INFO_FOOTER", $this->txt("hint_to_email"));
        $mail = $this->renderer->render(
            $this->ui_factory->link()->standard($email, "mailto:" . $email)
        );
        $tpl->setVariable("WBD_MAIL", $mail);

        $panel = $this->ui_factory->panel()->standard(
            $this->txt("gut_beraten_header"),
            [
                $this->ui_factory->legacy($tpl->get())
            ]
        );

        return $this->renderer->render($panel);
    }

    protected function renderBase() : string
    {
        $panel = $this->ui_factory->panel()->standard(
            $this->txt("gut_beraten_header"),
            $this->ui_factory->legacy($this->txt("no_infos_for_now"))
        );

        return $this->renderer->render($panel);
    }

    protected function renderWithOrderTimeTransmission(
        RoundTrip $modal,
        Standard $start_button
    ) : string {
        $panel = $this->ui_factory->panel()->standard(
            $this->txt("possible_actions"),
            [
                $this->ui_factory->legacy($this->txt("order_as_education_provider")),
                $start_button
            ]
        );

        return $this->renderer->render($modal) . $this->renderer->render($panel);
    }

    protected function getStartButton(RoundTrip $modal) : Standard
    {
        return $this->ui_factory
            ->button()
            ->standard($this->txt("start"), "#")
            ->withOnClick(
                $modal->getShowSignal()
        );
    }


    protected function getInputModal(ilPropertyFormGUI $form) : RoundTrip
    {
        $form_id = 'form_' . $form->getId();
        $submit = $this->ui_factory
            ->button()
            ->primary($this->txt('next'), "#")
            ->withOnLoadCode(function ($id) use ($form_id) {
                return "$('#{$id}').click(function() { $('#{$form_id}').submit(); return false; });";
            });

        $modal = $this->ui_factory->modal()->roundtrip(
            $this->txt('order_as_education_provider'),
            [
                $this->ui_factory->legacy($form->getHTML())
            ]
        )->withActionButtons([$submit]);

        return $modal;
    }

    protected function initForm(bool $set_values = false) : ilPropertyFormGUI
    {
        $form = new \ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, self::CMD_CONFIRM_ENTRIES));
        $form->setId(uniqid('form'));
        $form->setTitle($this->txt("wbd_form_title"));
        $form->setDescription($this->txt("wbd_form_description"));

        $nu = new ilTextInputGUI($this->txt("id_title"), self::F_WBD_ID);
        $nu->setMaxLength(self::ID_LENGTH);
        $nu->setSize(self::ID_LENGTH);
        $nu->setInfo($this->txt("id_info_txt"));
        $nu->setRequired(true);
        $nu->setValidationRegexp(self::WBD_ID_REGEXP);
        $form->addItem($nu);

        $cb = new ilCheckboxInputGUI($this->txt("agreement"), self::F_WBD_AGREEMENT);
        $cb->setOptionTitle(sprintf($this->txt("agreement_text"), $this->getDownloadLink()));
        $cb->setRequired(true);
        $cb->setValue(1);
        $form->addItem($cb);

        if ($set_values) {
            $wbd_id = ilSession::get(self::F_WBD_ID);
            $agreement = (bool) ilSession::get(self::F_WBD_AGREEMENT);
            $nu->setValue($wbd_id);
            $cb->setChecked($agreement);
        }

        return $form;
    }

    public function getConfirmModal() : RoundTrip
    {
        $f = $this->ui_factory;

        $back_link = $this->ctrl->getLinkTargetByClass(
            get_class($this),
            self::CMD_BACK_TO_ENTER,
            "",
            false,
            false
        );
        $back = $f->button()->standard($this->txt("back"), $back_link);

        $order_link = $this->ctrl->getLinkTargetByClass(
            get_class($this),
            self::CMD_SAVE_ENTRIES,
            "",
            false,
            false
        );
        $order = $f->button()->primary($this->txt("order"), $order_link);

        $form = new \ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, self::CMD_CONFIRM_ENTRIES));
        $form->setId(uniqid('form'));
        $form->setTitle($this->txt("check_input"));
        $form->setDescription($this->txt("back_info"));

        $ne = new ilNonEditableValueGUI($this->txt("id_title"));
        $ne->setValue(ilSession::get(self::F_WBD_ID));
        $form->addItem($ne);

        $ne = new ilNonEditableValueGUI();
        $ne->setValue($this->txt("positive_agreement"));
        $form->addItem($ne);

        $modal = $this->ui_factory->modal()->roundtrip(
            $this->txt('order_as_education_provider'),
            [
                $this->ui_factory->legacy($form->getHTML())
            ]
        )->withActionButtons([$back, $order]);

        return $modal;
    }

    protected function getDownloadLink() : string
    {
        $f = $this->ui_factory;
        $download_link = "";

        $path = $this->settings->getDocumentPath();
        if (!is_null($path) && $path != "") {
            $r = $this->renderer;

            $download = $this->ctrl->getLinkTargetByClass(
                get_class($this),
                self::CMD_DOWNLOAD_FILE,
                "",
                true,
                false
            );

            $download_link = $r->render($f->link()->standard($this->txt("file_link"), $download));
        }

        return $download_link;
    }

    protected function txt(string $code) : string
    {
        $txt = $this->txt;
        return $txt($code);
    }
}
