<?php
use \CaT\Plugins\CourseMailing;
use CaT\Plugins\CourseMailing\AutomaticMails\MailOccasionFreetext;

require_once("Services/Object/classes/class.ilObject.php");
require_once("Services/TMS/Table/TMSTableParentGUI.php");

/**
 * GUI for automatic mails
 *
 * @author Nils Haagen	<nils.haagen@concepts-and-training.de>
 */
class ilAutomaticMailsGUI extends TMSTableParentGUI
{
    use CourseMailing\ilTxtClosure;

    const CMD_SHOW = "show_automails";
    const CMD_PREVIEW = "preview";
    const CMD_MANUAL_MAIL = "manual_mail";
    const ASYNC_CMD_USER_MODAL = "user_modal";
    const ASYNC_CMD_OBJECT_MODAL = "object_modal";
    const F_TPL_IDENT = "f_template_ident";
    const F_TPL_OWNER_REF = "f_template_owner_ref";
    const F_USERS = "f_users";
    const TABLE_ID = "table_automails";
    const F_SELECT_ALL = "f_select_all";
    const F_ATTACHMENTS = "f_attachments";

    private static $SCHEDULED_OCCASION_CLASSES = [
        'CaT\Plugins\CourseMailing\AutomaticMails\MailOccasionInvite',
        'CaT\Plugins\MaterialList\Mailing\MaterialListOccasion',
        'CaT\Plugins\Accomodation\Mailing\AccomodationListOccasion',
        'CaT\Plugins\RoomSetup\Mailing\RoomServiceOccasion',
        'CaT\Plugins\RoomSetup\Mailing\RoomSetupOccasion'
    ];

    private static $OBJECT_CONFIGURED_OCCASION_CLASSES = [
        'CaT\Plugins\MaterialList\Mailing\MaterialListOccasion',
        'CaT\Plugins\Accomodation\Mailing\AccomodationListOccasion',
        'CaT\Plugins\RoomSetup\Mailing\RoomServiceOccasion',
        'CaT\Plugins\RoomSetup\Mailing\RoomSetupOccasion'
    ];


    /**
     * @var mixed
     */
    protected $parent_gui;

    /**
     * @var \ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var \ilAccess
     */
    protected $g_access;

    /**
     * @var \ilTemplate
     */
    protected $g_tpl;

    /**
     * @var ilActions
     */
    protected $actions;

    public function __construct($parent_gui, $actions, \Closure $txt)
    {
        global $DIC;
        $this->g_ctrl = $DIC->ctrl();
        $this->g_tpl = $DIC->ui()->mainTemplate();
        $this->g_access = $DIC->access();
        $this->parent_gui = $parent_gui;
        $this->actions = $actions;
        $this->txt = $txt;
    }

    /**
     * Delegate commands
     *
     * @throws \Exception
     * @return void
     */
    public function executeCommand()
    {
        $parent_gui_class = get_class($this->parent_gui);
        $cmd = $this->g_ctrl->getCmd();


        switch ($cmd) {
            case self::CMD_PREVIEW:
                $id = $_GET['mailtplid'];
                $this->preview($id);
                break;

            case self::CMD_MANUAL_MAIL:
                $this->sendManualMails();
                $this->show();
                break;

            case self::ASYNC_CMD_USER_MODAL:
                $mod = $this->userSelectionModal($_GET['ident']);
                global $DIC;
                echo $DIC->ui()->renderer()->renderAsync($mod);
                exit();


            case self::ASYNC_CMD_OBJECT_MODAL:
                $mod = $this->scheduledMailModal($_GET['ident'], $_GET['owner']);
                global $DIC;
                echo $DIC->ui()->renderer()->renderAsync($mod);
                exit();


            case self::CMD_SHOW:
            default:
                $this->show();
                break;
        }
    }

    /**
     * Get occasions registered at course and their respective mail templates.
     *
     * @return array<string, mixed>
     */
    private function getOccasionsAndTemplates()
    {
        $data = array();
        $occasions = $this->actions->getMailingOccasionsAtCourse();
        foreach ($occasions as $occasion) {
            $template_ident = $occasion->templateIdent();

            if ($template_ident == MailOccasionFreetext::TEMPLATE_IDENT) {
                continue;
            }

            $template_data = $this->actions->getMailTemplateDataByIdent($template_ident);
            $record = array(
                'ident' => $template_ident,
                'events' => $occasion->listEvents(),
                'id' => $template_data['id'],
                'title' => $template_data['title'],
                'subject' => $template_data['subject'],
                'scheduled' => false,
                'owner' => null,
                'occasion' => null
            );

            if (in_array(get_class($occasion), self::$SCHEDULED_OCCASION_CLASSES)) {
                $record['scheduled'] = true;
                $record['owner'] = (int) $occasion->owner()->getRefId();
                $record['occasion'] = $occasion;
            }

            $data[] = $record;
        }
        usort($data, array($this, "sortByIdent"));
        return $data;
    }

    /**
     * @param array<string,mixed> $a
     * @param array<string,mixed> $b
     * @return int
     */
    private function sortByIdent($a, $b)
    {
        return strnatcmp($a['ident'], $b['ident']);
    }


    private function getFirstOccuranceOfIdent($logs, $ident)
    {
        foreach ($logs as $log) {
            if ($log->getTemplateIdent() == $ident) {
                return $log;
            }
        }
        return null;
    }


    /**
     * command: show the GUI
     *
     * @return void
     */
    protected function show()
    {
        global $DIC;
        $factory = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        $data = $this->getOccasionsAndTemplates();
        $logs = $this->actions->getMailLogsForCourse(array('datetime','desc'));

        //build and inject modals and trigger buttons per line.
        $modals = array();
        $modal_async_url = $_SERVER['REQUEST_URI'];
        $base = substr($modal_async_url, 0, strpos($modal_async_url, '?') + 1);
        $query = parse_url($modal_async_url, PHP_URL_QUERY);
        parse_str($query, $params); //read query into array $params

        $now = new DateTime();

        foreach ($data as $iterator => $entry) {
            $last = $this->getFirstOccuranceOfIdent($logs, $entry['ident']);
            if ($last) {
                $data[$iterator]['last_sent'] = $last->getDateAsString();
            }

            $params['ident'] = $entry['ident'];
            $params['cmd'] = self::ASYNC_CMD_USER_MODAL;

            if ($entry['scheduled']) {
                $params['owner'] = $entry['owner'];
                $dat_next = $data[$iterator]['occasion']->getNextScheduledDate();
                if (!is_null($dat_next)) {
                    $data[$iterator]['send_next'] = $dat_next->format('Y-m-d');
                }

                if (in_array(get_class($data[$iterator]['occasion']), self::$OBJECT_CONFIGURED_OCCASION_CLASSES)) {
                    $params['cmd'] = self::ASYNC_CMD_OBJECT_MODAL;
                }
            }

            $modal_async_url = $base . http_build_query($params);
            $modal = $factory->modal()->roundtrip('', $factory->legacy(''))
                ->withAsyncRenderUrl($modal_async_url);

            $modals[] = $modal;

            $preview_lnk = $this->g_ctrl->getLinkTarget($this, self::CMD_PREVIEW) . '&mailtplid=' . $entry['id'];
            $dd_actions = array(
                $factory->button()->shy($this->txt('action_preview'), $preview_lnk),
                $factory->button()->shy($this->txt('action_manual_mail'), '#')
                    ->withOnClick($modal->getShowSignal())
            );
            $dd = $factory->dropdown()->standard($dd_actions)->withLabel($this->txt('actions'));
            $data[$iterator]['actions'] = $renderer->render($dd);
        }

        $table = $this->getTable();
        $table->setData($data);

        $this->g_tpl->setContent(
            $renderer->render($modals)
            . $table->getHtml()
        );
    }

    /**
     *
     */
    protected function getTable()
    {
        $table = $this->getTMSTableGUI();

        $table->setTitle($this->txt("table_automails_title"));
        $table->setRowTemplate("tpl.auto_mails_row.html", $this->actions->getPluginDirectory());
        $table->setFormAction($this->g_ctrl->getFormAction($this));
        $table->setTopCommands(false);
        $table->setExternalSegmentation(false);
        $table->setShowRowsSelector(true);

        $table->addColumn($this->txt("table_automails_template_ident"), false);
        $table->addColumn($this->txt("table_automails_template_id"), false);
        $table->addColumn($this->txt("table_automails_template_title"), false);
        $table->addColumn($this->txt("table_automails_template_subject"), false);
        $table->addColumn($this->txt("table_automails_onevents"), false);
        $table->addColumn($this->txt("table_automails_from_object"), false);
        $table->addColumn($this->txt("table_automails_sent_last"), false);
        $table->addColumn($this->txt("table_automails_sent_next"), false);
        $table->addColumn($this->txt("table_automails_actions"), false);

        return $table;
    }

    /**
     * @inheritdoc
     */
    protected function fillRow()
    {
        $settings = $this->actions->getSettings();
        return function ($table, $a_set) use ($settings) {
            $owner_title = '';
            if (!is_null($a_set['owner'])) {
                $owner_obj_id = \ilObject::_lookupObjId($a_set['owner']);
                $owner_title = \ilObject::_lookupTitle($owner_obj_id);
            }
            $tpl = $table->getTemplate();
            $tpl->setVariable("TEMPLATE_IDENT", $a_set['ident']);
            $tpl->setVariable("TEMPLATE_ID", $a_set['id']);
            $tpl->setVariable("TEMPLATE_TITLE", $a_set['title']);
            $tpl->setVariable("TEMPLATE_SUBJECT", $a_set['subject']);
            $tpl->setVariable("OCCASION_EVENTS", implode('<br>', $a_set['events']));

            $tpl->setVariable("FROM_OBJECT", $owner_title);

            $tpl->setVariable("MAIL_SENT_LAST", $a_set['last_sent']);

            $send_next = $a_set['send_next'];
            if ($settings->getPreventMailing()) {
                $send_next = "";
            }
            $tpl->setVariable("MAIL_SENT_NEXT", $send_next);
            $tpl->setVariable("ACTIONS", $a_set['actions']);
        };
    }

    /**
     * @inheritdoc
     */
    protected function tableCommand()
    {
        return self::CMD_SHOW;
    }

    /**
     * @inheritdoc
     */
    protected function tableId()
    {
        return self::TABLE_ID;
    }

    /**
     * command: show preview of mail
     *
     * @param int $tplid
     * @return void
     */
    protected function preview($tplid)
    {
        require_once 'Services/Mail/classes/Preview/class.ilMailPreviewGUI.php';
        require_once("Services/Mail/classes/Preview/ilPreviewFactory.php");

        global $ilToolbar;
        $ilToolbar->addButton($this->txt("back"), $this->g_ctrl->getLinkTarget($this, self::CMD_SHOW));

        $tpl = $this->actions->getMailTemplate($tplid);
        $gui = new ilMailPreviewGUI($tpl, new ilPreviewFactory());

        $this->g_tpl->setContent($gui->getHTML());
    }

    /**
     * command: send manual mails
     *
     * @return void
     */
    protected function sendManualMails()
    {
        $post = $_POST;
        $template_ident = $post[self::F_TPL_IDENT];

        if (array_key_exists(self::F_USERS, $post)) {
            $usr_ids = array_map(function ($id) {
                return (int) $id;
            }, $post[self::F_USERS]);
            $attachments = $post[self::F_ATTACHMENTS];
            if (!$attachments) {
                $attachments = array();
            }
            $this->actions->sendManualMailsForUsers($template_ident, $usr_ids, $attachments);
        } elseif (array_key_exists(self::F_TPL_OWNER_REF, $post)) {
            $obj_ref = (int) $post[self::F_TPL_OWNER_REF];
            $this->actions->sendManualMailsForObject($template_ident, $obj_ref, false);
        }
    }

    /**
     * Get a form to select users of the course
     *
     * @param string $ident
     */
    protected function userSelectionForm($ident)
    {
        require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
        $form = new ilPropertyFormGUI();
        $form->setId(uniqid('form'));
        $form->setFormAction($this->g_ctrl->getFormAction($this, self::CMD_MANUAL_MAIL));

        $item = new ilHiddenInputGUI(self::F_TPL_IDENT);
        $item->setValue($ident);
        $form->addItem($item);


        $section = new \ilFormSectionHeaderGUI();
        $section->setTitle($this->txt('manual_form_header_recipients'));
        $form->addItem($section);

        //get users
        $course_users = $this->actions->getMembersOfParentCourse();
        usort($course_users, function ($a, $b) {
            return strcmp(strtolower($a->getLastname()), strtolower($b->getLastname()));
        });

        foreach ($course_users as $user) {
            $label = sprintf(
                "%s, %s (%s)",
                $user->getLastname(),
                $user->getFirstname(),
                $user->getLogin()
            );
            $item = new ilCheckboxInputGUI('', self::F_USERS . '[]');
            $item->setOptionTitle($label);
            $item->setValue($user->getId());
            $form->addItem($item);
        }

        $section = new \ilFormSectionHeaderGUI();
        $section->setTitle($this->txt('manual_form_header_attachments'));
        $form->addItem($section);

        $possible_attachemnts = $this->actions->getAttachmentOptions();
        foreach ($possible_attachemnts as $key => $value) {
            $item = new ilCheckboxInputGUI('', self::F_ATTACHMENTS . '[]');
            $item->setOptionTitle($value);
            $item->setValue($key);
            $form->addItem($item);
        }

        return $form;
    }

    /**
     * Get a roundtrip-modal to select users
     *
     * @param string $ident
     * @return Modal
     */
    protected function userSelectionModal($ident)
    {
        global $DIC;
        $factory = $DIC->ui()->factory();
        $form = $this->userSelectionForm($ident);

        // Build a submit button (action button) for the modal footer
        $form_id = 'form_' . $form->getId();
        $submit = $factory->button()->primary($this->txt('manual_mail_submit'), '#')
            ->withOnLoadCode(function ($id) use ($form_id) {
                return "$('#{$id}').on('click', function(ev) {
					send_mails_manually('$form_id');
				})";
            });

        $select_all = $factory->button()
            ->standard($this->txt("manual_mail_select_all"), '#')
            ->withOnLoadCode(function ($id) use ($form_id) {
                return "$('#{$id}').on('click', function(ev) {
					checkall('$form_id');
				})";
            });

        $modal = $factory->modal()->roundtrip(
            $this->txt('modal_user_selection_title'),
            $factory->legacy(
                $form->getHTML()
                . $this->getSubmitJS()
                . $this->getSelectAllJS()
            )
        )->withActionButtons([$select_all, $submit]);

        return $modal;
    }


    /**
     * Get a "form" to display fixed params for scheduled mails.
     *
     * @param string $owner_ref_id
     */
    protected function scheduledMailForm($ident, $owner_ref_id)
    {
        require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
        require_once('./Services/Form/classes/class.ilNonEditableValueGUI.php');
        $form = new ilPropertyFormGUI();
        $form->setId(uniqid('form'));
        $form->setFormAction($this->g_ctrl->getFormAction($this, self::CMD_MANUAL_MAIL));

        $item = new ilHiddenInputGUI(self::F_TPL_IDENT);
        $item->setValue($ident);
        $form->addItem($item);

        $item = new ilHiddenInputGUI(self::F_TPL_OWNER_REF);
        $item->setValue($owner_ref_id);
        $form->addItem($item);

        $owner_obj_id = \ilObject::_lookupObjId($owner_ref_id);
        $owner_title = \ilObject::_lookupTitle($owner_obj_id);


        $ne = new ilNonEditableValueGUI($this->txt('manual_sched_owner_title'), 'manual_sched_owner_title');
        $ne->setValue($owner_title);
        $form->addItem($ne);

        return $form;
    }

    /**
     * Get a roundtrip-modal for scheduled mails with fixed recipients
     *
     * @param 	string 	$ident
     * @param 	int 	$owner
     * @return 	Modal
     */
    protected function scheduledMailModal($ident, $owner)
    {
        global $DIC;
        $factory = $DIC->ui()->factory();
        $form = $this->scheduledMailForm($ident, $owner);

        // Build a submit button (action button) for the modal footer
        $form_id = 'form_' . $form->getId();

        $submit = $factory->button()->primary($this->txt('manual_mail_submit'), '#')
            ->withOnLoadCode(function ($id) use ($form_id) {
                return "$('#{$id}').on('click', function(ev) {
					send_mails_manually('$form_id');
				})";
            });

        $modal = $factory->modal()->roundtrip(
            $this->txt('modal_scheduled_mail_title'),
            $factory->legacy($form->getHTML() . $this->getSubmitJS())
        )->withActionButtons([$submit]);

        return $modal;
    }


    /**
     * get the js preventing double submission
     * @return string
     */
    protected function getSubmitJS()
    {
        return "<script>

		function getModalFooter(form_id) {
			var frm = document.getElementById(form_id),
				modal = frm.parentNode,
				footer = modal.parentNode.getElementsByClassName('modal-footer')[0];
			return footer;
		}

		function noSubmitAction() {
			return false;
		}

		function send_mails_manually(form_id) {
			var submit_btn = getModalFooter(form_id).getElementsByClassName('btn-primary')[0];
			submit_btn.href='javascript:void(noSubmitAction());';
			submit_btn.innerHTML='...sending...';
			document.getElementById(form_id).submit();
		}
		</script>";
    }

    protected function getSelectAllJS()
    {
        return "<script>

		function checkall(form_id) {
			var frm = document.getElementById(form_id),
				checkboxes = frm.elements,
				check_all_btn = getModalFooter(form_id).firstElementChild;
				check = $(check_all_btn).attr('checked') ? false : true;

			for(var i=0; i < checkboxes.length; i++) {
				var ch = checkboxes[i];
				id = ch.id;
				if(id.indexOf('_users_') == 1) {
					ch.checked = check;
				}
			}

			$(check_all_btn).attr('checked', check);
			check_all_btn.innerHTML = check ?
				'" . $this->txt("manual_mail_unselect_all") . "' : '" . $this->txt("manual_mail_select_all") . "'
				;
		};
		</script>";
    }
}
