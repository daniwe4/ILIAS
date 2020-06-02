<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

if (!class_exists(ilCronJob::class)) {
    require_once "Services/Cron/classes/class.ilCronJob.php";
}

use ILIAS\TMS\CourseCreation\Request;
use CaT\Plugins\CourseCreation\RequestDB;
use CaT\Plugins\CourseCreation\SendMails;
use CaT\Plugins\CourseCreation\Mailing\Mailer;
use ILIAS\TMS\CourseCreation\Process;
use Psr\Log\LoggerInterface;

/**
* Implementation of the cron job
*/
class ilCourseCreationJob extends ilCronJob
{
    const ID = "tms_course_creation";

    /**
     * @var	RequestDB
     */
    protected $request_db;

    /**
     * @var SendMails
     */
    protected $send_mails;

    /**
     * @var	Process
     */
    protected $process;

    /**
     * @var	LoggerInterface
     */
    protected $logger;

    /**
     * @var ilTree
     */
    protected $tree;

    public function __construct(
        RequestDB $request_db,
        Process $process,
        SendMails $send_mails,
        LoggerInterface $logger,
        ilTree $tree
    ) {
        $this->request_db = $request_db;
        $this->process = $process;
        $this->send_mails = $send_mails;
        $this->logger = $logger;
        $this->tree = $tree;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return ilCourseCreationJob::ID;
    }

    /**
     * Is to be activated on "installation"
     *
     * @return boolean
     */
    public function hasAutoActivation()
    {
        return true;
    }

    /**
     * Can the schedule be configured?
     *
     * @return boolean
     */
    public function hasFlexibleSchedule()
    {
        return true;
    }

    /**
     * Get schedule type
     *
     * @return int
     */
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_IN_MINUTES;
    }

    /**
     * Get schedule value
     *
     * @return int|array
     */
    public function getDefaultScheduleValue()
    {
        return 5;
    }

    /**
     * This disables the cache for ente's provider.
     * It does not update properly in the crons's while-loop,
     * and thus CourseInformations might be empty for newly created Courses.
     * E.g., this can be seen in the EduTracking's IDD-Time.
     */
    protected function turnOffEnteCaching()
    {
        global $DIC;
        $DIC->extend("ente.provider_db", function ($provider_db, $c) {
            return new \CaT\Ente\ILIAS\ilProviderDB(
                $c["ilDB"],
                $c["tree"],
                $c["ilObjDataCache"]
            );
        });
    }

    /**
     * Processes the creation requests one after the other.
     *
     * @return \ilCronJobResult
     */
    public function run()
    {
        $this->turnOffEnteCaching();
        while (true) {
            $request = $this->request_db->getNextDueRequest();
            if ($request === null) {
                break;
            }
            try {
                $request = $this->process->run($request);
                $this->request_db->update($request);
                if ($this->shouldMailBeSend($request)) {
                    sleep(1);
                    $this->send_mails->sendSuccessMails($request);
                }
            } catch (\Exception $e) {
                $this->logger->error("Error when creating course:\n$e");
                $request = $request->withFinishedTS(new \DateTime());
                $this->request_db->update($request);
                if (is_null($request->getTargetRefId())) {
                    throw new \Exception("No course was created" . $e->getMessage());
                }
                $this->send_mails->sendFailMails($request, $e);
            }
            $this->ping();
        }

        $cron_result = new \ilCronJobResult();
        $cron_result->setStatus(\ilCronJobResult::STATUS_OK);
        return $cron_result;
    }

    protected function shouldMailBeSend(Request $request)
    {
        $crs_ref_id = $request->getCourseRefId();
        $xcps_id = array_shift($this->tree->getSubtree($this->tree->getNodeData($crs_ref_id), false, "xcps"));
        $xcps = ilObjectFactory::getInstanceByRefId($xcps_id);

        return !$xcps->getExtendedSettings()->getNoMail();
    }

    /**
     * Ping the cron manager.
     *
     * @return void
     */
    protected function ping()
    {
        \ilCronManager::ping($this->getId());
    }

    /**
     * Get the title of the job
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Course Creation';
    }
}
