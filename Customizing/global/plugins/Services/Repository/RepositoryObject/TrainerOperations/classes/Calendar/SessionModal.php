<?php

declare(strict_types=1);

namespace CaT\Plugins\TrainerOperations\Calendar;

use ILIAS\UI\Implementation\Component\Dropdown\Dropdown;
use CaT\Plugins\TrainerOperations\Aggregations\IliasRepository;
use CaT\Plugins\TrainerOperations\Aggregations\UserAuthority;
use CaT\Plugins\TrainerOperations\Aggregations\User;
use CaT\Plugins\TrainerOperations\AccessHelper;
use ILIAS\UI\Component as UIComponent;

use CaT\Ente\ILIAS\ilHandlerObjectHelper;
use ILIAS\TMS\CourseInfo;
use ILIAS\TMS\CourseInfoHelper;
use ILIAS\TMS\CourseAction;
use ILIAS\TMS\ActionBuilder;
use ILIAS\TMS\ActionBuilderUserHelper;

/**
 * Render the session modal.
 * Get CourseInfo and CourseActions via ente (CONTEXT_TEP_SESSION_DETAILS)
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class SessionModal
{
    use ilHandlerObjectHelper;
    use CourseInfoHelper;
    use ActionBuilderUserHelper;

    const ASYNC_CMD_SEARCH_USER = 'search_user';
    const CMD_ASSIGN_TUTORS_TO_SESSION = 'cmd_assign_session';
    const CMD_ASSIGN_TUTORS_TO_COURSE = 'cmd_assign_course';
    const F_ADD_TUTOR = 'f_addtutor';
    const F_ASSIGNS_TUTORS = 'f_assign';
    const F_SESSION_REF_ID = 'f_sess_ref';
    const F_COURSE_REF_ID = 'f_crs_ref';
    const F_TUTOR_ID = 'f_tid';
    const F_TUTOR_SOURCE = 'f_tutor_source';
    const F_TUTOR_NAME = 'f_tutor_name';
    const F_TUTOR_MAIL = 'f_tutor_mail';
    const F_TUTOR_PHONE = 'f_tutor_phone';

    /**
     * @var \Closure
     */
    protected $txt;

    /**
     * @var \ilTemplate
     */
    protected $session_form_template;

    /**
     * @var \ilTemplate
     */
    protected $tutor_add_form_tpl;

    /**
     * @var Factory
     */
    protected $ui_factory;

    /**
     * @var DefaultRenderer
     */
    protected $ui_renderer;

    /**
     * @var IliasRepository
     */
    protected $il_repo;

    /**
     * @var UserAuthority
     */
    protected $user_authority;

    /**
     * @var User
     */
    protected $user_utils;

    /**
     * @var int
     */
    protected $current_user_id;
    /**
     * @var AccessHelper
     */
    protected $access_helper;

    /**
     * @var array<int ,CourseInfo[]>
     */
    protected $course_info_cache = [];

    /**
     * @var array<int ,CourseAction[]>
     */
    protected $course_actions_cache = [];

    /**
     * @var ActionBuilder[] | null
     */
    protected $action_builders = null;

    public function __construct(
        \Closure $txt,
        \ilTemplate $session_form_template,
        \ilTemplate $tutor_add_form_tpl,
        \ILIAS\UI\Implementation\Factory $ui_factory,
        \ILIAS\UI\Implementation\DefaultRenderer $ui_renderer,
        IliasRepository $il_repo,
        UserAuthority $user_authority,
        User $user_utils,
        \ilCtrl $g_ctrl,
        int $current_user_id,
        AccessHelper $access_helper
    ) {
        $this->txt = $txt;
        $this->session_form_template = $session_form_template;
        $this->tutor_add_form_tpl = $tutor_add_form_tpl;
        $this->ui_factory = $ui_factory;
        $this->ui_renderer = $ui_renderer;
        $this->il_repo = $il_repo;
        $this->user_authority = $user_authority;
        $this->user_utils = $user_utils;
        $this->g_ctrl = $g_ctrl;
        $this->current_user_id = $current_user_id;
        $this->access_helper = $access_helper;
    }

    protected function txt(string $code) : string
    {
        assert('is_string($code)');
        $txt = $this->txt;
        return $txt($code);
    }

    public function getFor(int $session_ref, int $course_ref) : UIComponent\Modal\Roundtrip
    {
        $this->crs_ref_id = $course_ref;

        $details = $this->getDetailsOfCourse($course_ref);
        $sessions = $this->getSessionsForCourse($course_ref, $session_ref);
        $actions = $this->getActionsForCourseAndUser($course_ref, $this->current_user_id);

        $tpl = clone $this->session_form_template;
        $tpl->setVariable('SESSION_REF_ID', $session_ref);
        $tpl->setVariable('DETAILS', $this->ui_renderer->render($details));
        $tpl->setVariable('SESSIONS', $this->ui_renderer->render($sessions));
        $tpl->setVariable('ACTIONS', $this->ui_renderer->render($actions));

        if ($this->access_helper->mayEditMembersAtCourse($course_ref)) {
            $assign_frm = $this->getTutorAssignForm($session_ref, $course_ref);
            $add_frm = $this->getAddTutorForm($session_ref, $course_ref);

            $tpl->setVariable('ADD_TUTOR_FORM', $add_frm->get());
            $tpl->setVariable('ASSIGN_TUTOR_FORM', $assign_frm->getHTML());

            $submit = $this->ui_factory->button()->primary($this->txt("modal_session_submit"), '')
                ->withOnLoadCode(function ($id) use ($session_ref) {
                    return "$('#{$id}').on('click', function(ev) {
						TEPCalendar.submitTutorAssign('$session_ref');
					})";
                });

            $modal = $this->ui_factory->modal()->roundtrip(
                $this->txt('modal_session_title'),
                $this->ui_factory->legacy($tpl->get())
            )->withActionButtons([$submit]);
        } else {
            $modal = $this->ui_factory->modal()->roundtrip(
                $this->txt('modal_session_title'),
                $this->ui_factory->legacy($tpl->get())
            );
        }

        return $modal;
    }

    protected function getEntityRefId() : int
    {
        return $this->crs_ref_id;
    }
    protected function getDIC()
    {
        global $DIC;
        return $DIC;
    }

    protected function getDetailsOfCourse(int $course_ref_id) : UIComponent\Listing\Descriptive
    {
        $details = [];
        foreach ($this->courseInfo($course_ref_id) as $inf) {
            $details[$inf->getLabel()] = $inf->getValue();
        }

        $listing = $this->ui_factory->listing()->descriptive($details);
        return $listing;
    }

    /**
     * @return CourseInfo[]
     */
    protected function courseInfo(int $course_ref_id) : array //CourseInfo[]
    {
        if (!array_key_exists($course_ref_id, $this->course_info_cache)) {
            $this->course_info_cache[$course_ref_id] = $this->getCourseInfo(
                CourseInfo::CONTEXT_TEP_SESSION_DETAILS
            );
        }
        return $this->course_info_cache[$course_ref_id];
    }

    protected function getActionsForCourseAndUser(int $course_ref_id, int $usr_id) : Dropdown
    {
        $actions = [];
        foreach ($this->courseActions($course_ref_id, $usr_id) as $act) {
            if ($act->isAllowedFor($usr_id)) {
                $link = $act->getLink($this->g_ctrl, $usr_id);
                $actions[] = $this->ui_factory->button()->shy(
                    $act->getLabel(),
                    ''
                )->withOnLoadCode(function ($id) use ($link) {
                    return "$('#{$id}').on('click', function(ev) {
						javascript:window.open('$link');void(0);
					})";
                });
            }
        }
        return $this->ui_factory->dropdown()->standard($actions)->withLabel($this->txt("actions"));
    }

    /**
     * @return CourseAction[]
     */
    protected function courseActions(int $course_ref_id, int $usr_id) : array
    {
        if (!array_key_exists($course_ref_id, $this->course_actions_cache)) {
            $this->course_actions_cache[$course_ref_id] = $this->getActionsFor(
                ActionBuilder::CONTEXT_TEP_SESSION_DETAILS,
                $usr_id
            );
        }
        return $this->course_actions_cache[$course_ref_id];
    }

    /**
     * @return CourseAction[]
     */
    protected function getActionsFor(int $context, int $usr_id) : array
    {
        $action_builders = $this->getActionBuilder();
        $actions = [];
        foreach ($action_builders as $action_builder) {
            $actions[] = $action_builder->getCourseActionsFor($context, $usr_id);
        }
        $actions = $this->mergeActions($actions);
        ksort($actions);
        return $actions;
    }

    protected function getSessionsForCourse(
        int $course_ref_id,
        int $current_session_ref_id
    ) : UIComponent\Listing\Descriptive {
        $sess_objs = [];
        $session_ids = $this->il_repo->getAllChildrenOfByType($course_ref_id, 'sess');
        foreach ($session_ids as $sess_info) {
            $sess_ref_id = (int) $sess_info['ref_id'];
            $sess_objs[] = $this->il_repo->getInstanceByRefId($sess_ref_id);
        }

        usort($sess_objs, function ($a, $b) {
            $dat_a = $a->getFirstAppointment()->getStart()->getUnixTime();
            $dat_b = $b->getFirstAppointment()->getStart()->getUnixTime();
            return $dat_a > $dat_b;
        });


        $entries = [];
        foreach ($sess_objs as $sess_obj) {
            $title = $this->formatSessionTitle($sess_obj); //getPresentationTitle will render things like "Today" or "Tomorrow"
            if ($sess_obj->getRefId() === $current_session_ref_id) {
                $title = '<span class="current_session">' . $title . '</span>';
            }
            $entries[] = $title;
        }

        $session_listing = $this->ui_factory->listing()->unordered($entries);
        $listing = $this->ui_factory->listing()->descriptive([
            $this->txt('label_session_listing') => $session_listing
        ]);
        return $listing;
    }

    protected function formatSessionTitle(\ilObjSession $session) : string
    {
        $appointment = $session->getFirstAppointment();
        //TODO: time is +1h !!
        $start = (string) $appointment->getStart()->getUnixTime();
        $start = \DateTime::createFromFormat('U', $start);
        $end = (string) $appointment->getEnd()->getUnixTime();
        $end = \DateTime::createFromFormat('U', $end);

        //TODO: timezone of user instead of fixed tz!
        $tz = new \DateTimeZone('Europe/Berlin');
        $start->setTimezone($tz);
        $end->setTimezone($tz);

        $fullday = (bool) $appointment->isFullday();

        if ($start->format('Ymd') === $end->format('Ymd')) {
            $title = $start->format('d.m.Y'); //TODO: format date
            if ($fullday) {
                $title .= ', ' . $this->txt('fullday');
            } else {
                $title .= ', ' . $start->format('H:i')
                    . '-' . $end->format('H:i');
            }
        } else {
            $title .= ' ' . $start->format('H:i')
                . ' - ' . $end->format('d.m.Y H:i');
        }
        if (!$fullday) {
            $title .= ' ' . $this->txt('oclock');
        }

        if ($session->getTitle()) {
            $title .= '<br /><small>' . $session->getTitle() . '</small>';
        }

        return $title;
    }


    public function getTutorAssignForm(
        int $session_ref_id,
        int $crs_ref_id
    ) : \ilPropertyFormGUI {
        $form_id = uniqid('form');
        $form = new \ilPropertyFormGUI();
        $form->setId($form_id);

        $form_action = $this->g_ctrl->getFormActionByClass(
            strtolower('ilTrainerOperationsGUI'),
            self::CMD_ASSIGN_TUTORS_TO_SESSION
        );
        $form->setFormAction($form_action);

        $sec = new \ilFormSectionHeaderGUI();
        $sec->setTitle($this->txt('title_tutor_assign'));
        $form->addItem($sec);

        $hi = new \ilHiddenInputGUI(self::F_SESSION_REF_ID);
        $hi->setValue($session_ref_id);
        $form->addItem($hi);

        $session = $this->il_repo->getInstanceByRefId($session_ref_id);
        $possible_tutors = $this->il_repo->getTutorsAtCourse($crs_ref_id);
        $assigned_tutors_ids = $session->getAssignedTutorsIds();

        $post_var = self::F_TUTOR_SOURCE . '__' . $form_id;
        $tutor_opts = new \ilRadioGroupInputGUI($this->txt('event_tutor_source'), $post_var);

        $tutor_name = new \ilTextInputGUI($this->txt('tutor_name'), self::F_TUTOR_NAME);
        $tutor_name->setValue($session->getName());
        $tutor_name->setSize(20);
        $tutor_name->setMaxLength(70);

        $tutor_email = new \ilTextInputGUI($this->txt('tutor_email'), self::F_TUTOR_MAIL);
        $tutor_email->setValue($session->getEmail());
        $tutor_email->setSize(20);
        $tutor_email->setMaxLength(70);

        $tutor_phone = new \ilTextInputGUI($this->txt('tutor_phone'), self::F_TUTOR_PHONE);
        $tutor_phone->setValue($session->getPhone());
        $tutor_phone->setSize(20);
        $tutor_phone->setMaxLength(70);

        $tutor_opt_text = new \ilRadioOption($this->txt('event_tutor_source_manually'), $session::TUTOR_CFG_MANUALLY);
        $tutor_opt_text->addSubItem($tutor_name);
        $tutor_opt_text->addSubItem($tutor_email);
        $tutor_opt_text->addSubItem($tutor_phone);

        $tutor_opts->addOption($tutor_opt_text);

        $tutor_list = new \ilCheckboxGroupInputGUI($this->txt('event_tutor_selection'), self::F_ASSIGNS_TUTORS);
        foreach ($possible_tutors as $tutor) {
            $label = sprintf(
                "%s, %s <br><small>%s, %s</small>",
                $tutor->getLastname(),
                $tutor->getFirstname(),
                $tutor->getLogin(),
                $tutor->getEMail()
            );
            $id = $tutor->getId();
            $tutor_list->addOption(new \ilCheckboxOption($label, $id));
        }
        $tutor_list->setValue($assigned_tutors_ids);

        $tutor_opt_list = new \ilRadioOption($this->txt('event_tutor_source_from_course'), $session::TUTOR_CFG_FROMCOURSE);
        $tutor_opt_list->addSubItem($tutor_list);
        $tutor_opts->addOption($tutor_opt_list);
        $tutor_opts->setValue($tutor_source);
        $form->addItem($tutor_opts);

        $tutor_opts->setValue($session->getTutorSource());
        return $form;
    }


    public function getAddTutorForm(
        int $session_ref_id,
        int $crs_ref_id
    ) : \ilTemplate {
        $form_id = uniqid('form');
        $this->g_ctrl->setParameterByClass('ilTrainerOperationsGUI', self::F_SESSION_REF_ID, $session_ref_id);
        $this->g_ctrl->setParameterByClass('ilTrainerOperationsGUI', self::F_COURSE_REF_ID, $crs_ref_id);
        $form_action = $this->g_ctrl->getFormActionByClass(
            strtolower('ilTrainerOperationsGUI'),
            self::CMD_ASSIGN_TUTORS_TO_COURSE
        );
        $param = self::F_TUTOR_ID;
        $submit = $this->ui_factory->button()->standard($this->txt("add_tutor"), '')
            ->withOnLoadCode(function ($id) use ($form_id, $param) {
                return "$('#{$id}').on('click', function(ev) {
					TEPCalendar.submitTutorAdd('$form_id', '$param');
					return false;
				})";
            });

        $tpl = clone $this->tutor_add_form_tpl;
        $tpl->setVariable('FORM_ID', $form_id);
        $tpl->setVariable('FORM_ACTION', $form_action);
        $tpl->setVariable('FORM_TITLE', $this->txt('title_tutor_add'));
        $tpl->setVariable('FIELD_ID', self::F_ADD_TUTOR);

        $assigned = array_map(
            function ($usr) {
                return (int) $usr->getId();
            },
            $this->il_repo->getTutorsAtCourse($crs_ref_id)
        );

        $tutors = array_filter(
            $this->user_authority->getTrainers(),
            function ($id) use ($assigned) {
                return in_array($id, $assigned) === false;
            }
        );

        $tutors = $this->user_utils->sortUsrIdsByUserLastname($tutors);

        foreach ($tutors as $tutor_id) {
            $label = $this->user_utils->getDisplayName($tutor_id, true);
            $tpl->setCurrentBlock('option');
            $tpl->setVariable('VALUE', $tutor_id);
            $tpl->setVariable('LABEL', $label);
            $tpl->parseCurrentBlock();
        }
        if (count($tutors) === 0) {
            $submit = $submit->withUnavailableAction();
        }
        $tpl->setVariable('SUBMIT_BUTTON', $this->ui_renderer->render($submit));

        return $tpl;
    }
}
