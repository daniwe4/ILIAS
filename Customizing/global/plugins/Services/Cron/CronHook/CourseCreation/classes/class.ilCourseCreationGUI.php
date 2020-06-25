<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> */


require_once("Modules/Course/classes/class.ilObjCourseAccess.php");
require_once("Services/TMS/PluginObjectFactory.php");

use CaT\Plugins\CourseCreation\RequestBuilder;
use CaT\Plugins\CourseCreation\ilRequestDB;

use ILIAS\TMS\CourseCreation;
use ILIAS\TMS\Wizard;

/**
 * GUI that leads user through steps to create a course.
 *
 * @ilCtrl_isCalledBy ilCourseCreationGUI: ilRepositoryGUI
 */
class ilCourseCreationGUI
{
    use CourseCreation\LinkHelper;
    use \PluginObjectFactory;

    const CREATE_COURSE_COMMAND = "create_course_from_template";
    const OPEN_REQUEST_WAITING_INTERVAL = 30000;

    /**
     * @var	\ilGlobalTemplateInterface
     */
    protected $g_tpl;

    /**
     * @var	\ilObjUser
     */
    protected $g_user;

    /**
     * @var	\ilLanguage
     */
    protected $g_lng;

    /**
     * @var	\ilCtrl
     */
    protected $g_ctrl;

    /**
     * @var	\ilTree
     */
    protected $g_tree;

    /**
     * @var	int
     */
    protected $crs_ref_id;

    /**
     * @var	int
     */
    protected $crs_obj_id;

    /**
     * @var	Wizard\Content
     */
    protected $content;

    /**
     * @var CourseCreation\ILIASBindings
     */
    protected $ilias_bindings;

    /**
     * @var ilRbacReview
     */
    protected $g_rbacreview;

    public function __construct($_, $a_ref_id)
    {
        if (\ilObject::_lookupType($a_ref_id, true) != "crs") {
            throw new \LogicException("CourseCreation: May only copy courses.");
        }

        $this->crs_ref_id = (int) $a_ref_id;
        $this->crs_obj_id = (int) \ilObject::_lookupObjId($this->crs_ref_id);

        global $DIC;
        $this->g_tpl = $DIC["tpl"];
        $this->g_user = $DIC->user();
        $this->g_ctrl = $DIC->ctrl();
        $this->g_lng = $DIC->language();
        $this->g_lng->loadLanguageModule('tms');
        $this->g_tree = $DIC->repositoryTree();
        $this->g_rbacreview = $DIC["rbacreview"];

        $this->content = new Wizard\Content(
            "Error",
            "Something went wrong. This GUI should have created some real content but appearantly didn't..."
        );
    }

    /**
     * @return	\ilCtrl
     */
    protected function getCtrl()
    {
        return $this->g_ctrl;
    }

    /**
     * @return \ilLanguage
     */
    protected function getLng()
    {
        return $this->g_lng;
    }

    /**
     * @inheritdoc
     */
    protected function getUser()
    {
        return $this->g_user;
    }

    /**
     * @inheritdoc
     */
    protected function sendInfo($message)
    {
        \ilUtil::sendInfo($message, true);
    }

    /**
     * This does nothing but is required to make this work with ilRepositoryGUI.
     */
    public function setCreationMode($creation_mode)
    {
        assert(!$creation_mode);
    }

    /* Get the translations-decorator.
     *
     * @return  \ILIAS\TMS\Translations
     */
    protected function getTranslations()
    {
        $trans = new \ILIAS\TMS\TranslationsImpl(
            array(
                Wizard\Player::TXT_TITLE => $this->getLng()->txt('create_course_from_template'),
                Wizard\Player::TXT_CONFIRM => $this->getLng()->txt('create_course'),
                Wizard\Player::TXT_CANCEL => $this->getLng()->txt('cancel'),
                Wizard\Player::TXT_NEXT => $this->getLng()->txt('btn_next'),
                Wizard\Player::TXT_PREVIOUS => $this->getLng()->txt('btn_previous'),
                Wizard\Player::TXT_OVERVIEW_DESCRIPTION => $this->getLng()->txt('create_course_overview_description'),
                Wizard\Player::TXT_NO_STEPS_AVAILABLE => $this->getLng()->txt('no_steps_available'),
                Wizard\Player::TXT_ABORTED => $this->getLng()->txt('process_aborted')
            )
        );
        return $trans;
    }

    public function executeCommand()
    {
        if (!\ilObjCourseAccess::_checkAccess(self::CREATE_COURSE_COMMAND, "copy", $this->crs_ref_id, $this->crs_obj_id)) {
            throw new \ilException("Cannot create course based on template '{$this->crs_ref_id}'");
        }

        if (!$this->canCreateRequest(
            $this->g_user,
            $this->getRbac(),
            $this->getCourseCreationPlugin()
            )
        ) {
            if ($this->maybeShowRequestInfo($this->getCourseCreationPlugin(), self::OPEN_REQUEST_WAITING_INTERVAL)) {
                $link = $this->g_ctrl->getLinkTargetByClass($this->getParentGUIs(), $this->getParentCommand(), "ref_id=" . $this->getParentRefId(), false, false);
                $link = str_replace("#ref_id", "&ref_id", $link);
                \ilUtil::redirect($link);
            }
        }

        $this->g_ctrl->saveParameter($this, ["parent_guis", "parent_cmd", "parent_ref_id"]);

        $ilias_bindings = new CourseCreation\ILIASBindings(
            $this->g_ctrl,
            $this,
            $this->getParentGUIs(),
            $this->getParentCommand(),
            $this->getParentRefId(),
            $this->getTranslations()
        );

        global $DIC;
        $state_db = new Wizard\SessionStateDB();
        // TODO: remove this by something real...
        $request_db = new ilRequestDB($DIC->database());
        $request_builder = new RequestBuilder($request_db);
        $request_builder
            ->setCourseRefId($this->crs_ref_id)
            ->setNewParentRefId((int) $this->g_tree->getParentId($this->crs_ref_id));
        $wizard = new CourseCreation\Wizard(
            $DIC,
            $request_builder,
            (int) $this->g_user->getId(),
            $this->getSessionId(),
            $this->crs_ref_id,
            0 // TODO: some timestamp here...
        );
        $player = new Wizard\Player(
            $ilias_bindings,
            $wizard,
            $state_db
        );
        $cmd = $this->g_ctrl->getCmd();
        if ($cmd == '') {
            $cmd = 'next';
            $_POST = null;
        }
        if ($cmd === self::CREATE_COURSE_COMMAND) {
            $cmd = "start";
            $_POST = null;
        }

        $this->content = $player->run($cmd, $_POST);
        $this->ilias_bindings = $ilias_bindings;
        assert('is_string($this->content)');
    }

    public function getHTML()
    {
        return $this->content;
    }

    /**
     * Get the GUI from where the user was directed here.
     *
     * @return	string[]
     */
    protected function getParentGUIs()
    {
        $parent_guis = $_GET["parent_guis"];
        if (preg_match("/^\w+([.]\w+)*$/", $parent_guis) === false) {
            return ["ilRepositoryGUI"];
        }
        return explode(".", $parent_guis);
    }

    /**
     * Get the command on the parent GUI from where the user was directed here.
     *
     * @return	string
     */
    protected function getParentCommand()
    {
        $parent_cmd = $_GET["parent_cmd"];
        if (preg_match("/^\w+$/", $parent_cmd) === false) {
            return "frameset";
        }
        return $parent_cmd;
    }

    /**
     * Get the command on the parent GUI from where the user was directed here.
     *
     * @return	string
     */
    protected function getParentRefId()
    {
        $parent_ref_id = $_GET["parent_ref_id"];
        if (!is_numeric($parent_ref_id)) {
            return 1;
        }
        return (int) $parent_ref_id;
    }

    /**
     * Get the session id of the current user.
     *
     * @return string
     */
    protected function getSessionId()
    {
        $session = \ilSession::_duplicate($_COOKIE["PHPSESSID"]);
        if ($session === false) {
            throw new \RuntimeException("Could not duplicate Session {$_COOKIE["PHPSESSID"]}");
        }
        return $session;
    }

    protected function getRbac()
    {
        return new RbacImpl($this->g_rbacreview);
    }
}
