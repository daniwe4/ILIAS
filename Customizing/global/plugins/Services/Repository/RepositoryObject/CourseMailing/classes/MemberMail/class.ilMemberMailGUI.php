<?php

declare(strict_types=1);

use \CaT\Plugins\CourseMailing\Surroundings\Surroundings;
use \CaT\Plugins\CourseMailing\AutomaticMails\CourseMailHandler;
use \CaT\Plugins\CourseMailing\RoleMapping\RoleMapping;
use \ILIAS\TMS\Mailing\TMSMailClerk;

/**
 * @ilCtrl_Calls ilMemberMailGUI: ilMailAttachmentGUI
 */
class ilMemberMailGUI extends TMSTableParentGUI
{
    const CMD_SHOW_MEMBERS = "showMembers";
    const CMD_CONFIRM_INVITATION = "confirmInvitationMail";
    const CMD_CONFIRM_BOOKING = "confirmBookingMail";
    const CMD_CONFIRM_CANCELLATION = "confirmCancellationMail";
    const CMD_SHOW_FREETEXT_FORM = "showFreetextForm";
    const CMD_SEND_MAIL = "sendMail";
    const CMD_EDIT_ATTACHMENTS = "editAttachments";
    const CMD_RETURN_FROM_ATTACHEMNTS = "returnFromAttachments";

    const P_USR_IDS = "usr_ids";
    const P_USR_IDS_MAILDATA = "rcp_to";
    const P_MAIL_TYPE = "mail_type";
    const P_FREETEXT_SUBJECT = "freetext_subject";
    const P_FREETEXT_BODY = "freetext_body";
    const P_FREETEXT_SUBJECT_MAILDATA = "m_subject";
    const P_FREETEXT_BODY_MAILDATA = "m_message";
    const P_ATTACHMENTS = "attachments";

    const EVENT_INVITATION_SINGLE = 'mail_invitation_single';

    const TPL_IDENT_BOOKING = "B01";
    const TPL_IDENT_CANCELLATION = "C01";

    const MAIL_TYPE_INVITATION = "invitation";
    const MAIL_TYPE_BOOKING = "booking";
    const MAIL_TYPE_CANCELLATION = "cancellation";
    const MAIL_TYPE_FREETEXT = "freetext";

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilGlobalTemplateInterface
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilFormatMail
     */
    protected $umail;

    /**
     * @var ilFileDataMail
     */
    protected $mfile;

    /**
     * @var Surroundings
     */
    protected $surroundings;

    /**
     * @var Closure
     */
    protected $txt;

    /**
     * @var TMSMailClerk
     */
    protected $clerk;

    /**
     * @var string
     */
    protected $plugin_dir;

    /**
     * @var RoleMapping[]
     */
    protected $role_mappings;

    /**
     * @var ilMailAttachmentGUI
     */
    protected $attachemnt_gui;

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $tpl,
        ilLanguage $lng,
        ilObjUser $user,
        ilFormatMail $umail,
        ilFileDataMail $mfile,
        Surroundings $surroundings,
        Closure $txt,
        TMSMailClerk $clerk,
        string $plugin_dir,
        array $role_mappings,
        ilMailAttachmentGUI $attachemnt_gui
    ) {
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->user = $user;
        $this->umail = $umail;
        $this->mfile = $mfile;
        $this->surroundings = $surroundings;
        $this->txt = $txt;
        $this->clerk = $clerk;
        $this->plugin_dir = $plugin_dir;
        $this->role_mappings = $role_mappings;
        $this->attachemnt_gui = $attachemnt_gui;

        $this->lng->loadLanguageModule("mail");
    }

    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass();
        switch ($next_class) {
            case 'ilmailattachmentgui':
                $this->ctrl->setReturn($this, "returnFromAttachments");
                $this->ctrl->forwardCommand($this->attachemnt_gui);
                break;
            default:
                switch ($cmd) {
                    case self::CMD_SHOW_MEMBERS:
                        $this->showMembers();
                        break;
                    case self::CMD_CONFIRM_BOOKING:
                        $this->showConfirmForm(
                            self::MAIL_TYPE_BOOKING,
                            $this->txt("send_booking_mail")
                        );
                        break;
                    case self::CMD_CONFIRM_CANCELLATION:
                        $this->showConfirmForm(
                            self::MAIL_TYPE_CANCELLATION,
                            $this->txt("send_cancellation_mail")
                        );
                        break;
                    case self::CMD_CONFIRM_INVITATION:
                        $this->showConfirmForm(
                            self::MAIL_TYPE_INVITATION,
                            $this->txt("send_invitation_mail")
                        );
                        break;
                    case self::CMD_SHOW_FREETEXT_FORM:
                        $this->showFreetextForm();
                        break;
                    case self::CMD_SEND_MAIL:
                        $this->sendMail();
                        break;
                    case self::CMD_EDIT_ATTACHMENTS:
                        $this->editAttachments();
                        break;
                    case self::CMD_RETURN_FROM_ATTACHEMNTS:
                        $this->returnFromAttachments();
                        break;
                    default:
                        throw new Exception("Unknown comand: " . $cmd);
                }
        }
    }

    protected function showMembers()
    {
        $table = $this->getTMSTableGUI();
        $table->setTitle($this->txt("members"));
        $table->setRowTemplate("tpl.member_mail_row.html", $this->plugin_dir);
        $table->setFormAction($this->ctrl->getFormAction($this));
        $table->setSelectAllCheckbox(self::P_USR_IDS);
        $table->setDefaultOrderField("lastname");
        $table->setDefaultOrderDirection("asc");
        $table->determineOffsetAndOrder();
        $table->setExternalSegmentation(false);
        $table->setShowRowsSelector(true);

        $this->addColumsTo($table);
        $this->addMultiCommandsTo($table);

        $table->setData(
            $this->getMembersOfCourse(
            $table->getOrderField(),
            $table->getOrderDirection()
        )
        );

        $this->tpl->setContent($table->getHTML());
    }

    protected function showConfirmForm(string $mail_type, string $title)
    {
        $post = $_POST;

        if (!$this->memberSelected($post)) {
            ilUtil::sendInfo($this->txt("no_user_selected"), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_MEMBERS);
        }

        $form = new ilConfirmationGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setHeaderText($title);

        $usr_ids = $post[self::P_USR_IDS];
        $usr_data = $this->getUsrDataFor($usr_ids);
        foreach ($usr_data as $usr) {
            $form->addItem(
                "",
                "",
                sprintf(
                    $this->txt("recipient_confirm_line"),
                    $usr["lastname"],
                    $usr["firstname"],
                    $usr["login"]
                )
            );
        }

        $form->addHiddenItem(self::P_USR_IDS, base64_encode(serialize($usr_ids)));
        $form->addHiddenItem(self::P_MAIL_TYPE, $mail_type);

        $form->setConfirm($this->txt("manual_mail_submit"), self::CMD_SEND_MAIL);
        $form->setCancel($this->txt("cancel"), self::CMD_SHOW_MEMBERS);

        $this->tpl->setContent($form->getHTML());
    }

    protected function showFreetextForm($mail_data = [])
    {
        $post = $_POST;
        if (!$this->memberSelected($post) && count($mail_data) == 0) {
            ilUtil::sendInfo($this->txt("no_user_selected"), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_MEMBERS);
        }

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->txt("form_free_text"));

        if (count($mail_data) > 0) {
            $usr_ids = array_map(
                "intval",
                $this->getUsrIdsFromConfirmPost($mail_data[self::P_USR_IDS_MAILDATA])
            );
        } else {
            $usr_ids = $post[self::P_USR_IDS];
        }

        $usr_data = $this->getUsrDataFor($usr_ids);
        $usr_data = array_map(
            function ($usr) {
                return $usr["lastname"] . ", " . $usr["firstname"] . "(" . $usr["login"] . ")";
            },
            $usr_data
        );
        $recipients = join("<br />", $usr_data);

        $ne = new ilNonEditableValueGUI($this->txt("recipients"), "", true);
        $ne->setValue($recipients);
        $form->addItem($ne);

        $ti = new ilTextInputGUI($this->txt("subject"), self::P_FREETEXT_SUBJECT);
        if (isset($mail_data[self::P_FREETEXT_SUBJECT_MAILDATA]) && $mail_data[self::P_FREETEXT_SUBJECT_MAILDATA] != "") {
            $ti->setValue($mail_data[self::P_FREETEXT_SUBJECT_MAILDATA]);
        }
        $form->addItem($ti);

        $att = new ilMailFormAttachmentPropertyGUI($this->lng->txt('add'));
        if (is_array($mail_data[self::P_ATTACHMENTS]) && count($mail_data[self::P_ATTACHMENTS])) {
            foreach ($mail_data[self::P_ATTACHMENTS] as $data) {
                if (is_file($this->mfile->getMailPath() . '/' . $this->user->getId() . "_" . $data)) {
                    $hidden = new ilHiddenInputGUI('attachments[]');
                    $form->addItem($hidden);
                    $size = filesize($this->mfile->getMailPath() . '/' . $this->user->getId() . "_" . $data);
                    $label = $data . " [" . ilUtil::formatSize($size) . "]";
                    $att->addItem($label);
                    $hidden->setValue(urlencode($data));
                }
            }
        }
        $form->addItem($att);

        $ta = new ilTextAreaInputGUI($this->txt("body"), self::P_FREETEXT_BODY);
        if (isset($mail_data[self::P_FREETEXT_BODY_MAILDATA]) && $mail_data[self::P_FREETEXT_BODY_MAILDATA] != "") {
            $ta->setValue($mail_data[self::P_FREETEXT_BODY_MAILDATA]);
        }
        $ta->setRows(10);
        $form->addItem($ta);

        require_once 'Services/Mail/classes/Form/class.ilManualPlaceholderInputGUI.php';
        $placeholders = new ilManualPlaceholderInputGUI(self::P_FREETEXT_BODY);
        $placeholders->setAdviseText(sprintf($this->lng->txt('placeholders_advise'), '<br />'));
        $context = ilMailTemplateContextService::getTemplateContextById("crs_context_automatic");
        foreach ($context->getPlaceholders() as $key => $value) {
            $placeholders->addPlaceholder($value['placeholder'], $value['label']);
        }
        $form->addItem($placeholders);

        $hi = new ilHiddenInputGUI(self::P_USR_IDS);
        $hi->setValue(base64_encode(serialize($usr_ids)));
        $form->addItem($hi);

        $hi = new ilHiddenInputGUI(self::P_MAIL_TYPE);
        $hi->setValue(self::MAIL_TYPE_FREETEXT);
        $form->addItem($hi);

        $form->addCommandButton(self::CMD_SEND_MAIL, $this->txt("manual_mail_submit"));
        $form->addCommandButton(self::CMD_SHOW_MEMBERS, $this->txt("cancel"));

        $this->tpl->setContent($form->getHTML());
    }

    protected function sendMail()
    {
        $post = $_POST;

        $mail_type = $post[self::P_MAIL_TYPE];
        switch ($mail_type) {
            case self::MAIL_TYPE_INVITATION:
                $this->sendInvitation($post);
                break;
            case self::MAIL_TYPE_BOOKING:
                $this->sendBooking($post);
                break;
            case self::MAIL_TYPE_CANCELLATION:
                $this->sendCancellation($post);
                break;
            case self::MAIL_TYPE_FREETEXT:
                $this->sendFreetext($post);
                break;
            default:
                throw new Exception("Unknown mailtype: " . $mail_type);
        }
    }

    public function editAttachments()
    {
        $post = $_POST;
        $files = array();
        if (is_array($post[self::P_ATTACHMENTS])) {
            foreach ($post[self::P_ATTACHMENTS] as $value) {
                $files[] = urldecode($value);
            }
        }

        $usr_ids = $post[self::P_USR_IDS];
        // Note: For security reasons, ILIAS only allows Plain text messages.
        $this->umail->savePostData(
            $this->user->getId(),
            $files,
            $usr_ids,
            null,
            null,
            $_POST["m_type"],
            null,
            ilUtil::securePlainString($_POST[self::P_FREETEXT_SUBJECT]),
            ilUtil::securePlainString($_POST[self::P_FREETEXT_BODY]),
            null,
            ilMailFormCall::getContextId(),
            ilMailFormCall::getContextParameters()
        );

        $this->ctrl->redirectByClass("ilMailAttachmentGUI");
    }

    public function returnFromAttachments()
    {
        $mail_data = $this->umail->getSavedData();
        $this->showFreetextForm($mail_data);
    }

    protected function sendBooking(array $post)
    {
        $usr_ids = $this->getUsrIdsFromConfirmPost($post[self::P_USR_IDS]);
        $this->sendManualMailsForUsers(
            self::TPL_IDENT_BOOKING,
            $usr_ids,
            false
        );

        ilUtil::sendSuccess($this->txt("booking_send"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_MEMBERS);
    }

    protected function sendCancellation(array $post)
    {
        $usr_ids = $this->getUsrIdsFromConfirmPost($post[self::P_USR_IDS]);
        $this->sendManualMailsForUsers(
            self::TPL_IDENT_CANCELLATION,
            $usr_ids,
            false
        );

        ilUtil::sendSuccess($this->txt("cancellation_send"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_MEMBERS);
    }

    protected function sendInvitation(array $post)
    {
        $usr_ids = array_map(
            "intval",
            $this->getUsrIdsFromConfirmPost($post[self::P_USR_IDS])
        );

        foreach ($usr_ids as $usr_id) {
            $template_infos = $this->getInvitationTemplatesFor($usr_id);

            foreach ($template_infos as $template_info) {
                $this->sendManualMailsForUsers(
                    $template_info["title"],
                    [$usr_id],
                    $template_info["attachments"]
                );
            }
        }

        ilUtil::sendSuccess($this->txt("invitation_send"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_MEMBERS);
    }

    protected function sendFreetext(array $post)
    {
        $usr_ids = array_map(
            "intval",
            $this->getUsrIdsFromConfirmPost($post[self::P_USR_IDS])
        );

        $subject = $post[self::P_FREETEXT_SUBJECT];
        $body = $post[self::P_FREETEXT_BODY];

        $attachemnts = [];
        if (isset($post[self::P_ATTACHMENTS])) {
            $attachemnts = $this->decodeAttachmentFiles((array) $post[self::P_ATTACHMENTS]);
        }

        $this->sendManualMailsForUsers(
            ilCourseMailingPlugin::EVENT_FREETEXT,
            $usr_ids,
            $attachemnts,
            true,
            $subject,
            $body
        );

        ilUtil::sendSuccess($this->txt("freetext_send"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_MEMBERS);
    }

    protected function getUsrIdsFromConfirmPost(string $usr_ids) : array
    {
        return unserialize(base64_decode($usr_ids));
    }

    protected function memberSelected(array $post) : bool
    {
        return (isset($post[self::P_USR_IDS]) && count($post[self::P_USR_IDS]) > 0);
    }

    /**
     * @param int $usr_id
     * @return array
     */
    protected function getInvitationTemplatesFor(int $usr_id) : array
    {
        $ret = [];
        $roles = $this->getRoleIdsOfUser($usr_id);
        foreach ($this->role_mappings as $mapping) {
            if (!in_array($mapping->getRoleId(), $roles) || $mapping->getTemplateId() == 0) {
                continue;
            }

            $template = $this->surroundings->getMailTemplate($mapping->getTemplateId());
            if (!$template) {
                continue;
            }

            $ret[] = [
                "title" => $template->getTitle(),
                "attachments" => $mapping->getAttachmentIds()
            ];
        }

        return $ret;
    }

    protected function addColumsTo(ilTMSTableGUI $table)
    {
        $table->addColumn("", "", "1", true);
        $table->addColumn($this->txt("lastname"), "lastname");
        $table->addColumn($this->txt("firstname"), "firstname");
        $table->addColumn($this->txt("login"), "login");
        $table->addColumn($this->txt("roles"), "roles");
    }

    protected function addMultiCommandsTo(ilTMSTableGUI $table)
    {
        $table->addMultiCommand(self::CMD_CONFIRM_INVITATION, $this->txt("sendInvitation"));
        $table->addMultiCommand(self::CMD_CONFIRM_BOOKING, $this->txt("sendBooking"));
        $table->addMultiCommand(self::CMD_CONFIRM_CANCELLATION, $this->txt("sendCancellation"));
        $table->addMultiCommand(self::CMD_SHOW_FREETEXT_FORM, $this->txt("showFreetextForm"));
    }

    protected function getMembersOfCourse(string $sort, string $direction) : array
    {
        $users = $this->surroundings->getMembersOfParentCourse();

        $fc_sort = null;
        switch ($sort) {
            case "lastname":
                $fc_sort = function (ilObjUser $a, ilObjUser $b) use ($direction) {
                    if ($direction === "asc") {
                        return strcasecmp($a->getLastname(), $b->getLastname());
                    }
                    return strcasecmp($b->getLastname(), $a->getLastname());
                };
                break;
            case "firstname":
                $fc_sort = function (ilObjUser $a, ilObjUser $b) use ($direction) {
                    if ($direction === "asc") {
                        return strcasecmp($a->getFirstname(), $b->getFirstname());
                    }
                    return strcasecmp($b->getFirstname(), $a->getFirstname());
                };
                break;
            case "login":
                $fc_sort = function (ilObjUser $a, ilObjUser $b) use ($direction) {
                    if ($direction === "asc") {
                        return strcasecmp($a->getLogin(), $b->getLogin());
                    }
                    return strcasecmp($b->getLogin(), $a->getLogin());
                };
                break;
            case "roles":
                $fc_sort = function (ilObjUser $a, ilObjUser $b) use ($direction) {
                    $a_roles = $this->surroundings->getRoleSortingFor((int) $a->getId());
                    $b_roles = $this->surroundings->getRoleSortingFor((int) $b->getId());
                    if ($direction === "asc") {
                        return strcasecmp($a_roles, $b_roles);
                    }
                    return strcasecmp($b_roles, $a_roles);
                };
                break;
        }

        if (!is_null($fc_sort)) {
            uasort($users, $fc_sort);
        }
        return $users;
    }



    /**
     * @inheritDoc
     */
    protected function fillRow()
    {
        return function (ilTMSTableGUI $table, ilObjUser $usr) {
            $tpl = $table->getTemplate();

            $tpl->setCurrentBlock("checkb");
            $tpl->setVariable("POST_VAR", self::P_USR_IDS);
            $tpl->setVariable("USER_ID", $usr->getId());
            $tpl->parseCurrentBlock();

            $tpl->setCurrentBlock("column");
            $tpl->setVariable("VALUE", $usr->getLastname());
            $tpl->parseCurrentBlock();

            $tpl->setCurrentBlock("column");
            $tpl->setVariable("VALUE", $usr->getFirstname());
            $tpl->parseCurrentBlock();

            $tpl->setCurrentBlock("column");
            $tpl->setVariable("VALUE", $usr->getLogin());
            $tpl->parseCurrentBlock();

            $roles = $this->getLabledRolesofUser((int) $usr->getId());
            $tpl->setCurrentBlock("column");
            $tpl->setVariable("VALUE", join("<br />", $roles));
            $tpl->parseCurrentBlock();
        };
    }

    /**
     * @inheritDoc
     */
    protected function tableCommand()
    {
        return self::CMD_SHOW_MEMBERS;
    }

    /**
     * @inheritDoc
     */
    protected function tableId()
    {
        return "mail";
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }

    protected function sendManualMailsForUsers(
        string $template_ident,
        array $usr_ids,
        $attachments = null,
        bool $is_freetext = false,
        string $subject = "",
        string $body = ""
    ) {
        $occasions = $this->getMailingOccasionsAtCourse();
        $occasion = null;
        foreach ($occasions as $mail_occasion) {
            if ($mail_occasion->templateIdent() == $template_ident) {
                $occasion = $mail_occasion;
            }
        }
        if ($occasion) {
            $mails = array();
            $event = 'manual';
            $params = array(
                'crs_ref_id' => $this->surroundings->getParentCourseRefId(),
                'usr_id' => null,
                'attachments' => $attachments
            );

            if ($is_freetext) {
                $params["subject"] = $subject;
                $params["body"] = $body;
            }

            foreach ($usr_ids as $usr_id) {
                $params['usr_id'] = $usr_id;
                $mails = array_merge($mails, $occasion->getMails($event, $params));
            }
            $this->clerk->process($mails, $event);
        }
    }

    protected function getMailingOccasionsAtCourse() : array
    {
        $crs_ref = $this->surroundings->getParentCourseRefId();
        $mailhandler = new CourseMailHandler($crs_ref);
        return $mailhandler->getMailingOccasions();
    }

    protected function getRolesOfUser(int $usr_id)
    {
        return $this->surroundings->getRolesForMember($usr_id);
    }

    protected function getRoleIdsOfUser(int $usr_id)
    {
        return $this->surroundings->getRoleIdsOfUser($usr_id);
    }

    protected function getLabledRolesofUser(int $usr_id)
    {
        $ret = [];

        foreach ($this->getRolesOfUser($usr_id) as $role) {
            if (substr($role, 0, 3) === 'il_') {
                $ret[] = $this->txt($role);
            } else {
                $ret[] = $role;
            }
        }

        return $ret;
    }

    protected function getUsrDataFor(array $usr_ids) : array
    {
        $ret = [];
        foreach ($usr_ids as $usr_id) {
            $ret[] = ilObjUser::_lookupName($usr_id);
        }
        return $ret;
    }

    protected function decodeAttachmentFiles(array $files)
    {
        $decodedFiles = array();

        foreach ($files as $value) {
            if (is_file($this->mfile->getMailPath() . '/' . $this->user->getId() . '_' . urldecode($value))) {
                $decodedFiles[] = $this->mfile->getAbsoluteAttachmentPoolPathByFilename(urldecode($value));
            }
        }

        return $decodedFiles;
    }
}
