<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilSessionAppEventListener implements ilAppEventListener
{
    /**
     * @var ilDBInterface
     */
    private $database;

    /**
     * @var ilObjectDataCache
     */
    private $objectDataCache;

    /**
     * @var ilLogger
     */
    private $logger;

    /**
     * @var string
     */
    private $component;

    /**
     * @var string
     */
    private $event;

    /**
     * @var array
     */
    private $parameters;
    
    // cat-tms-patch start
    /**
     * @var int[]
     */
    protected static $ref_ids = [];

    /**
     * @var bool
     */
    protected static $prevent_execution = false;
    // cat-tms-patch end

    /**
     * @param ilDBInterface $db
     * @param ilObjectDataCache $objectDataCache
     * @param ilLogger $logger
     */
    public function __construct(
        \ilDBInterface $db,
        \ilObjectDataCache $objectDataCache,
        \ilLogger $logger
    ) {
        $this->database = $db;
        $this->objectDataCache = $objectDataCache;
        $this->logger = $logger;
    }

    /**
     * @param string $component
     * @return \ilSessionAppEventListener
     */
    public function withComponent($component)
    {
        $clone = clone $this;

        $clone->component = $component;

        return $clone;
    }

    /**
     * @param string $event
     * @return \ilSessionAppEventListener
     */
    public function withEvent($event)
    {
        $clone = clone $this;

        $clone->event = $event;

        return $clone;
    }

    /**
     * @param array $parameters
     * @return \ilSessionAppEventListener
     */
    public function withParameters(array $parameters)
    {
        $clone = clone $this;

        $clone->parameters = $parameters;

        return $clone;
    }

    /**
     * Handle an event in a listener.
     *
     * @param    string $a_component component, e.g. "Modules/Forum" or "Services/User"
     * @param    string $a_event event e.g. "createUser", "updateUser", "deleteUser", ...
     * @param    array $a_parameter parameter array (assoc), array("name" => ..., "phone_office" => ...)
     */
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
        // cat-tms-patch start
        if (self::$prevent_execution) {
            return;
        }
        // cat-tms-patch end

        global $DIC;

        $listener = new static(
            $DIC->database(),
            $DIC['ilObjDataCache'],
            $DIC->logger()->sess()
        );

        $listener
            ->withComponent($a_component)
            ->withEvent($a_event)
            ->withParameters($a_parameter)
            ->handle();
    }

    public function handle()
    {
        // cat-tms-patch start
        if (
            $this->component === 'Modules/Course' &&
            $this->event === 'update'
        ) {
            $this->updateSessionAppointments();
        }

        if (
            $this->component === 'Services/User' &&
            $this->event === 'deleteUser'
        ) {
            $this->deleteTutorFromAllLecture();
        }

        if (
            $this->component === 'Services/AccessControl' &&
            $this->parameters['type'] === 'crs' &&
            $this->isTutorRole()
        ) {
            switch ($this->event) {
                case 'deassignUser':
                    $this->deleteTutorFromLecture();
                    break;
                case 'assignUser':
                    $this->setTutorAsLecture();
                    break;
            }
        }
        // cat-tms-patch end

        if ('Modules/Session' !== $this->component) {
            return;
        }

        try {
            if ('register' === $this->event) {
                $this->handleRegisterEvent();
            } elseif ('enter' === $this->event) {
                $this->handleEnteredEvent();
            } elseif ('unregister' === $this->event) {
                $this->handleUnregisterEvent();
            }
        } catch (\ilException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function handleRegisterEvent()
    {
        $type = ilSessionMembershipMailNotification::TYPE_REGISTER_NOTIFICATION;

        $this->sendMail($type);
    }

    private function handleEnteredEvent()
    {
        $type = ilSessionMembershipMailNotification::TYPE_ENTER_NOTIFICATION;

        $this->sendMail($type);
    }

    private function handleUnregisterEvent()
    {
        $type = ilSessionMembershipMailNotification::TYPE_UNREGISTER_NOTIFICATION;

        $this->sendMail($type);
    }

    private function fetchRecipientParticipants()
    {
        $object = new ilEventParticipants($this->parameters['obj_id']);

        $recipients = array();
        $participants = $object->getParticipants();
        foreach ($participants as $id => $participant) {
            if ($participant['notification_enabled'] === true) {
                $recipients[] = $id;
            }
        }

        return $recipients;
    }

    /**
     * @param array $recipients
     * @param $type
     * @throws ilException
     */
    private function sendMail($type)
    {
        $recipients = $this->fetchRecipientParticipants();
        if (array() !== $recipients) {
            $notification = new ilSessionMembershipMailNotification();
            $notification->setRecipients($recipients);
            $notification->setType($type);
            $notification->setRefId($this->parameters['ref_id']);

            $notification->send($this->parameters['usr_id']);
        }
    }

    // cat-tms-patch start
    /**
     * Update sessions relative to course
     */
    protected function updateSessionAppointments()
    {
        $crs = $this->parameters['object'];
        $crs_start = $crs->getCourseStart();
        $sessions = $this->getSessionsOfCourse($crs->getRefId());

        foreach ($sessions as $session) {
            $appointment = $session->getFirstAppointment();
            $start_time = $appointment->getStart()->get(IL_CAL_FKT_DATE, "H:i:s", "UTC");
            $end_time = $appointment->getEnd()->get(IL_CAL_FKT_DATE, "H:i:s", "UTC");
            $offset = $appointment->getDaysOffset();

            $start_date = $this->createDateTime(date("Y-m-d"), $start_time);
            $end_date = $this->createDateTime(date("Y-m-d"), $end_time);

            if ($crs_start) {
                $crs_start->increment(ilDateTime::DAY, --$offset);

                $date = $crs_start->get(IL_CAL_FKT_DATE, "Y-m-d");
                $start_date = $this->createDateTime($date, $start_time);
                $end_date = $this->createDateTime($date, $end_time);
            }

            $appointment->setStart($start_date);
            $appointment->setEnd($end_date);
            $appointment->update();
        }
    }

    protected function deleteTutorFromLecture() : void
    {
        $usr_id = (int) $this->parameters["usr_id"];
        $crs_obj_id = (int) $this->parameters["obj_id"];
        $crs_ref_id = $this->getReferenceId($crs_obj_id);
        foreach ($this->getSessionsOfCourse($crs_ref_id) as $session) {
            $assigned_tutors = $session->getAssignedTutorsIds();
            $assigned_tutors = array_filter($assigned_tutors, function ($id) use ($usr_id) {
                return $id != $usr_id;
            });
            $session->setAssignedTutors($assigned_tutors);

            $session->update();

            if (count($assigned_tutors) == 0) {
                $session->setTutorSource(ilObjSession::TUTOR_CFG_MANUALLY);
            }

            $session->update();
        }
    }

    protected function deleteTutorFromAllLecture() : void
    {
        $usr_id = (int) $this->parameters["usr_id"];
        foreach ($this->getAllSessions() as $session) {
            $assigned_tutors = $session->getAssignedTutorsIds();
            $assigned_tutors = array_filter($assigned_tutors, function ($id) use ($usr_id) {
                return $id != $usr_id;
            });
            $session->setAssignedTutors($assigned_tutors);

            $session->update();

            if (count($assigned_tutors) == 0) {
                $session->setTutorSource(ilObjSession::TUTOR_CFG_MANUALLY);
            }

            $session->update();
        }
    }

    protected function setTutorAsLecture() : void
    {
        $usr_id = (int) $this->parameters["usr_id"];
        $crs_obj_id = (int) $this->parameters["obj_id"];
        $crs_ref_id = $this->getReferenceId($crs_obj_id);
        foreach ($this->getSessionsOfCourse($crs_ref_id) as $session) {
            $assigned_tutors = $session->getAssignedTutorsIds();
            array_push($assigned_tutors, $usr_id);
            $session->setAssignedTutors($assigned_tutors);
            $session->setTutorSource(ilObjSession::TUTOR_CFG_FROMCOURSE);
            $session->update();
        }
    }
    
    /**
     * Find sessions underneath course
     * @return ilObjSession[]
     */
    protected function getSessionsOfCourse(int $crs_ref_id) : array
    {
        global $DIC;

        $g_tree = $DIC->repositoryTree();
        $ret = array();
        $sessions = $g_tree->getChildsByType($crs_ref_id, "sess");

        foreach ($sessions as $session) {
            $ret[] = ilObjectFactory::getInstanceByRefId($session['ref_id']);
        }

        return $ret;
    }

    /**
     * Get all sessions
     *
     * @return ilSession[]
     */
    protected function getAllSessions() : array
    {
        $ret = array();
        foreach (\ilObject::_getObjectsByType("sess") as $sess) {
            $ret[] = \ilObjectFactory::getInstanceByObjId($sess["obj_id"]);
        }

        return $ret;
    }

    protected function getReferenceId(int $obj_id) : int
    {
        $ref_ids = ilObject::_getAllReferences($obj_id);
        return (int) array_shift($ref_ids);
    }

    /**
     * Creates a DateTime object in UTC timezone
     */
    protected function createDateTime(string $date, string $time) : ilDateTime
    {
        return new ilDateTime($date . " " . $time, IL_CAL_DATETIME, 'UTC');
    }

    /**
    * Disable this event-handler
    */
    public static function preventExecution(bool $status)
    {
        self::$prevent_execution = $status;
    }

    protected function isTutorRole() : bool
    {
        return $this->parameters['role_id'] ==
            $this->getDefaultTutorRoleFor((int) $this->parameters['role_id']);
    }

    protected function getDefaultTutorRoleFor(int $crs_id) : ?int
    {
        $ref_ids = \ilObject::_getAllReferences($crs_id);
        $ref_id = (int) array_shift($ref_ids);
        $crs = \ilObjectFactory::getInstanceByRefId((int) $ref_id);

        return $crs->getDefaultTutorRole();
    }
    // cat-tms-patch end
}
