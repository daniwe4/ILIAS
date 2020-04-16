<?php
include_once("./Services/Repository/classes/class.ilRepositoryObjectPlugin.php");
require_once __DIR__ . "/../vendor/autoload.php";

use CaT\Plugins\BookingApprovals\ilPluginActions;
use CaT\Plugins\BookingApprovals\Approvals;
use CaT\Plugins\BookingApprovals\Events\Events as ApprovalEvents;
use CaT\Plugins\BookingApprovals\Events\EventHandler as ApprovalEventsHandler;
use CaT\Plugins\BookingApprovals\Player\RequestPlayer;
use CaT\Plugins\BookingApprovals\Player\StepsProcessorFactory;
use CaT\Plugins\BookingApprovals\Utils\CourseUtils;
use CaT\Plugins\BookingApprovals\Utils\OrguUtils;
use CaT\Plugins\BookingApprovals\Utils\IliasWrapper;
use CaT\Plugins\BookingApprovals\Mailing\MailFactory;
use ILIAS\TMS\Booking;
use ILIAS\TMS\Wizard;
use ILIAS\TMS\Mailing\TMSMailClerk;

/**
 * Plugin base class. Keeps all information the plugin needs.
 *
 * @author
 * @copyright Extended GPL, see LICENSE
 */
class ilBookingApprovalsPlugin extends ilRepositoryObjectPlugin
{
    const COPY_OPERATION_ID = 58;

    /**
     * @var ilPluginActions
     */
    protected $plugin_actions;

    /**
     * @var Approvals/Actions
     */
    protected $approval_actions;

    /**
     * @var Approvals/ApprovalDB
     */
    protected $approvals_db;

    /**
     * @var ILIAS\TMS\Mailing\TMSMailClerk
     */
    protected $tms_mail_clerk;

    /**
     * @var Mailing/MailFactory
     */
    protected $mail_factory;

    /**
     * @var IliasWrapper
     */
    protected $ilias_wrapper;

    /**
     * Decides if this repository plugin can be copied.
     *
     * @return bool
     */
    public function allowCopy()
    {
        return true;
    }

    /**
     * Get an instance of ilPluginActions.
     *
     * @return 	ilPluginActions
     */
    public function getPluginActions()
    {
        if ($this->plugin_actions == null) {
            $this->plugin_actions = new ilPluginActions();
        }
        return $this->plugin_actions;
    }

    /**
     * Get the name of the Plugin
     *
     * @return 	string
     */
    public function getPluginName()
    {
        return "BookingApprovals";
    }

    /**
     * Get a closure to get txts from plugin.
     *
     * @return \Closure
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
    }

    /**
     * Assign permission copy to current plugin
     *
     * @param 	int 	$type_id
     * @param 			$db
     * @return 	int
     */
    protected function assignCopyPermissionToPlugin($type_id, $db)
    {
        $ops = array(self::COPY_OPERATION_ID);

        foreach ($ops as $op) {
            // check whether type exists in object data, if not, create the type
            if (!$this->permissionIsAssigned($type_id, $op, $db)) {
                $db->manipulate(
                    "INSERT INTO" . PHP_EOL
                    . "    rbac_ta" . PHP_EOL
                    . "    (typ_id, ops_id)" . PHP_EOL
                    . "VALUES" . PHP_EOL
                    . "    ("
                    . $db->quote($type_id, "integer") . ","
                    . $db->quote($op, "integer")
                    . "    )" . PHP_EOL
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function beforeActivation()
    {
        parent::beforeActivation();
        global $DIC;
        $db = $DIC->database();

        $type = $this->getId();

        if (!$this->isRepositoryPlugin($type)) {
            throw new ilPluginException("Object plugin type must start with an x. Current type is " . $type . ".");
        }

        $type_id = $this->getTypeId($type, $db);
        if (!$type_id) {
            $type_id = $this->createTypeId($type, $db);
        }

        $this->assignCopyPermissionToPlugin($type_id, $db);

        return true;
    }

    /**
     * Create a new entry in object data.
     *
     * @param 	string 	$type
     * @param 			$db
     * @return 	int
     */
    protected function createTypeId($type, $db)
    {
        $type_id = $db->nextId("object_data");
        $db->manipulate(
            "INSERT INTO" . PHP_EOL
            . "    object_data" . PHP_EOL
            . "    (" . PHP_EOL
            . "        obj_id," . PHP_EOL
            . "        type," . PHP_EOL
            . "        title," . PHP_EOL
            . "        description," . PHP_EOL
            . "        owner," . PHP_EOL
            . "        create_date," . PHP_EOL
            . "        last_update" . PHP_EOL
            . "    )" . PHP_EOL
            . "VALUES" . PHP_EOL
            . "    ("
            . $db->quote($type_id, "integer") . ","
            . $db->quote("typ", "text") . ","
            . $db->quote($type, "text") . ","
            . $db->quote("Plugin " . $this->getPluginName(), "text") . ","
            . $db->quote(-1, "integer") . ","
            . $db->quote(ilUtil::now(), "timestamp") . ","
            . $db->quote(ilUtil::now(), "timestamp")
            . "    )" . PHP_EOL
        );

        return $type_id;
    }

    /**
     * Check current plugin is repository plugin.
     *
     * @param 	string 	$type
     * @return 	bool
     */
    protected function isRepositoryPlugin($type)
    {
        return substr($type, 0, 1) == "x";
    }

    /**
     * Get id of current type.
     *
     * @param 	string 		$type
     * @param 				$db
     * @return 	int|null
     */
    protected function getTypeId($type, $db)
    {
        $set = $db->query(
            "SELECT" . PHP_EOL
            . "    obj_id" . PHP_EOL
            . "FROM" . PHP_EOL
            . "    object_data" . PHP_EOL
            . "WHERE" . PHP_EOL
            . "    type = " . $db->quote("typ", "text") . PHP_EOL
            . "AND" . PHP_EOL
            . "    title = " . $db->quote($type, "text") . PHP_EOL
        );

        if ($db->numRows($set) == 0) {
            return null;
        }

        $rec = $db->fetchAssoc($set);
        return $rec["obj_id"];
    }

    /**
     * Checks whether permission is not assigned to plugin.
     *
     * @param 	int 		$type_id
     * @param 	int 		$op_id
     * @param 				$db
     * @return 	bool
     */
    protected function permissionIsAssigned($type_id, $op_id, $db)
    {
        $set = $db->query(
            "SELECT" . PHP_EOL
            . "    count(typ_id) as cnt" . PHP_EOL
            . "FROM" . PHP_EOL
            . "    rbac_ta" . PHP_EOL
            . "WHERE" . PHP_EOL
            . "    typ_id = " . $db->quote($type_id, "integer") . PHP_EOL
            . "AND" . PHP_EOL
            . "    ops_id = " . $db->quote($op_id, "integer") . PHP_EOL
        );

        $rec = $db->fetchAssoc($set);

        return $rec["cnt"] > 0;
    }

    /**
     * Defines custom uninstall action like delete table or something else.
     *
     * @return 	void
     */
    protected function uninstallCustom()
    {
    }


    /**
     * Get storage for BookingRequests/Approvals
     */
    public function getApprovalsDB() : Approvals\ApprovalDB
    {
        if (!$this->approvals_db) {
            global $DIC;
            $this->approvals_db = new Approvals\ilDB($DIC->database());
        }
        return $this->approvals_db;
    }

    /**
     * Get actions for Approvals and BookingRequests
     */
    public function getApprovalActions() : Approvals\Actions
    {
        if (!$this->approval_actions) {
            $this->approval_actions = new Approvals\Actions(
                $this->getApprovalsDB(),
                $this->getApprovalEvents(),
                $this->getOrguUtils()
            );
        }
        return $this->approval_actions;
    }

    protected function getApprovalEvents() : ApprovalEvents
    {
        global $DIC;
        $g_event_handler = $DIC['ilAppEventHandler'];
        $ilias_wrapper = $this->getIliasWrapper();
        return new ApprovalEvents($g_event_handler, $ilias_wrapper);
    }

    /**
     * Get some utils for accessing the course or its booking modalities.
     */
    public function getCourseUtils() : CourseUtils
    {
        if (!$this->course_utils) {
            global $DIC;
            $tree = $DIC->repositoryTree();
            $obj_definition = $DIC["objDefinition"];
            $ilias_wrapper = $this->getIliasWrapper();
            $this->course_utils = new CourseUtils($tree, $obj_definition, $ilias_wrapper);
        }
        return $this->course_utils;
    }

    /**
     * Get utils for orgunit handling.
     */
    public function getOrguUtils() : OrguUtils
    {
        if (!$this->orgu_utils) {
            require_once("Services/TMS/Positions/TMSPositionHelper.php");
            require_once("Modules/OrgUnit/classes/Positions/UserAssignment/class.ilOrgUnitUserAssignmentQueries.php");
            $tms_pos_helper = new TMSPositionHelper(ilOrgUnitUserAssignmentQueries::getInstance());
            $this->orgu_utils = new OrguUtils($tms_pos_helper);
        }
        return $this->orgu_utils;
    }

    public function getIliasWrapper() : IliasWrapper
    {
        if (!$this->ilias_wrapper) {
            $this->ilias_wrapper = new IliasWrapper();
        }
        return $this->ilias_wrapper;
    }

    /**
     * Get the player for the booking-process.
     */
    public function getRequestPlayer(
        Wizard\ILIASBindings $ilias_bindings,
        Wizard\Wizard $wizard,
        Wizard\StateDB $state_db
    ) : Wizard\Player {
        return new RequestPlayer(
            $ilias_bindings,
            $wizard,
            $state_db,
            $this->getApprovalActions(),
            $this->getCourseUtils()
        );
    }

    protected function getStepsProcessorFactory() : StepsProcessorFactory
    {
        global $DIC;
        if (!$this->steps_processor_factory) {
            $this->steps_processor_factory = new StepsProcessorFactory(
                $DIC
            );
        }
        return $this->steps_processor_factory;
    }

    protected function getTMSMailer() : TMSMailClerk
    {
        if (!$this->tms_mail_clerk) {
            require_once("./Services/TMS/Mailing/classes/ilTMSMailing.php");
            $mailing = new \ilTMSMailing();
            $this->tms_mail_clerk = $mailing->getClerk();
        }
        return $this->tms_mail_clerk;
    }

    protected function getMailFactory() : MailFactory
    {
        if (!$this->mail_factory) {
            $this->mail_factory = new MailFactory(
            );
        }
        return $this->mail_factory;
    }

    protected function getApprovalEventsHandler() : ApprovalEventsHandler
    {
        if (!$this->approvals_event_handler) {
            $this->approvals_event_handler = new ApprovalEventsHandler(
                $this->getApprovalsDB(),
                $this->getOrguUtils(),
                $this->getStepsProcessorFactory(),
                $this->getMailFactory(),
                $this->getTMSMailer(),
                $this->getCourseUtils(),
                $this->getApprovalEvents()
            );
        }
        return $this->approvals_event_handler;
    }

    /**
     * Handle events: delegate to Events\EventHandler.
     */
    public function handleEvent(string $a_component, string $a_event, $a_parameter)
    {
        if (!in_array($a_component, [
            ApprovalEvents::EVENT_COMPONENT,
            'Modules/Course'
        ])) {
            return;
        }
        if ($a_component === ApprovalEvents::EVENT_COMPONENT) {
            $handler = $this->getApprovalEventsHandler();
            $handler->handle($a_event, $a_parameter);
        }
        if ($a_component === 'Modules/Course'
            /*
            && in_array($a_event, [
                'xxxxx'
                //delete
                //booking_period over
                //offline -- really for offline?
            ])
            */
        ) {
            //invalidate all requests for this course.
            //var_dump($a_event);
        }
    }

    public function hasUserOpenRequestOnCourse(int $usr_id, int $crs_id)
    {
        $approval_db = $this->getApprovalsDB();
        return $approval_db->hasUserOpenRequestOnCourse($usr_id, $crs_id);
    }
}
