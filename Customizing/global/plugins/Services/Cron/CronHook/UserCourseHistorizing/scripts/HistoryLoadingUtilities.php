<?php




require_once 'Customizing/global/plugins/Services/Cron/CronHook/UserCourseHistorizing/classes/class.ilUserCourseHistorizingPlugin.php';
require_once "Services/Tracking/classes/class.ilLPStatus.php";
require_once "Services/Tracking/classes/class.ilLPStatusWrapper.php";

require_once 'Modules/Course/classes/class.ilCourseWaitingList.php';
require_once 'Modules/Course/classes/class.ilCourseParticipants.php';
require_once 'Modules/Course/classes/class.ilObjCourse.php';


require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/CourseClassification/classes/class.ilObjCourseClassification.php';
class HistoryLoadingUtilities
{
    protected $plugin;

    public function __construct()
    {
        global $DIC;
        $this->plugin = new ilUserCourseHistorizingPlugin();
        $this->tree = $DIC['tree'];
    }

    public static function getInstance()
    {
        return new self();
    }

    public function historizeCourseData()
    {
        foreach ($this->getCourseRefIds() as $ref_id) {
            $crs = new ilObjCourse($ref_id);
            $crs->update();
        }
        $this->historizeCourseClassificationData();
        $this->historizeCrsMembersData();
    }

    protected function getCourseRefIds()
    {
        $return = [];
        foreach (array_map(
            function ($rec) {
                return (int) $rec['obj_id'];
            },
            ilObject::_getObjectsByType('crs')
        ) as $crs_id) {
            $return = array_merge($return, ilObject::_getAllReferences($crs_id));
        }
        return array_unique($return);
    }

    public function historizeCopySettings()
    {
        foreach ($this->getCourseRefIds() as $ref_id) {
            $payload = ['parent' => new ilObjCourse($ref_id),'ref_id' => $ref_id];
            if (count($this->tree->getSubtree($this->tree->getNodeData($ref_id), false, 'xcps')) > 0) {
                $type = 'updateCopySettings';
            } else {
                $type = 'deleteCopySettings';
            }
            $payload['type'] = $type;
            $this->plugin->handleEvent('Plugin/CopySettings', $type, $payload);
        }
    }

    public function historizeCrsParticipations()
    {
        foreach ($this->getCourseRefIds() as $ref_id) {
            $crs = new ilObjCourse($ref_id);
            $this->historizeCrsBookingStatus($crs);
            $this->historizeCrsParticipationStatus($crs);
        }
    }


    public function historizeSessions()
    {
        foreach ($this->getSessionReferences() as $ref_id) {
            $sess = new \ilObjSession($ref_id);
            $sess->update();
        }
    }


    protected function historizeCrsBookingStatus($crs)
    {
        $waiting_list = new ilCourseWaitingList($crs->getId());
        foreach ($waiting_list->getAllUsers() as $data) {
            $this->plugin->handleEvent(
                'Modules/Course',
                'addToWaitingList',
                [	'usr_id' => (int) $data['usr_id'],
                    'obj_id' => (int) $crs->getId()
                ]
            );
        }

        $participants = ilCourseParticipants::getInstanceByObjId($crs->getId());
        foreach ($participants->getMembers() as $usr_id) {
            $this->plugin->handleEvent(
                'Services/AccessControl',
                'assignUser',
                [	"type" => 'crs',
                        "usr_id" => (int) $usr_id,
                        'obj_id' => (int) $crs->getId(),
                        'role_id' => (int) $crs->getDefaultMemberRole()
                        ]
            );
        }
    }

    protected function historizeCrsParticipationStatus($crs)
    {
        $crs_id = (int) $crs->getId();
        foreach (ilLPStatusWrapper::_getNotAttempted((int) $crs->getId()) as $usr_id) {
            $this->plugin->handleEvent(
                "Services/Tracking",
                "updateStatus",
                [	"obj_id" => $crs_id,
                        "usr_id" => (int) $usr_id,
                        "status" => ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM
                        ]
            );
        }
        foreach (ilLPStatusWrapper::_getInProgress($crs_id) as $usr_id) {
            $this->plugin->handleEvent(
                "Services/Tracking",
                "updateStatus",
                [	"obj_id" => $crs_id,
                        "usr_id" => (int) $usr_id,
                        "status" => ilLPStatus::LP_STATUS_IN_PROGRESS_NUM
                        ]
            );
        }
        foreach (ilLPStatusWrapper::_getCompleted($crs_id) as $usr_id) {
            $this->plugin->handleEvent(
                "Services/Tracking",
                "updateStatus",
                [	"obj_id" => $crs_id,
                        "usr_id" => (int) $usr_id,
                        "status" => ilLPStatus::LP_STATUS_COMPLETED_NUM
                        ]
            );
        }
        foreach (ilLPStatusWrapper::_getFailed($crs_id) as $usr_id) {
            $this->plugin->handleEvent(
                "Services/Tracking",
                "updateStatus",
                [	"obj_id" => $crs_id,
                        "usr_id" => (int) $usr_id,
                        "status" => ilLPStatus::LP_STATUS_FAILED_NUM
                        ]
            );
        }
    }


    public function historizeCrsMembersData()
    {
        foreach ($this->getCMReferences() as $ref_id) {
            $cm = ilObjectFactory::getInstanceByRefId($ref_id);
            $parent = $cm->getParentCourse();
            if ($cm->getSettings()->getClosed() && !is_null($parent)) {
                $cm_actions = $cm->getActions();
                $p_id = (int) $parent->getId();
                foreach ($cm_actions->getMemberWithSavedLPSatus()
                    as $member) {
                    $minutes = $member->getIDDLearningTime();
                    $lp_value = $member->getLPValue();
                    if (!is_null($minutes)) {
                        $cm_actions->throwEvent(
                            $p_id,
                            $member->getUserId(),
                            $minutes,
                            $lp_value
                        );
                    }
                }
            }
        }
    }

    public function historizeEduTrackingData()
    {
        foreach ($this->getETReferences() as $ref_id) {
            $et = \ilObjectFactory::getInstanceByRefId($ref_id);
            $cfg_wbd = $et->getConfigWBD();
            if ($cfg_wbd !== null && $cfg_wbd->getAvailable()) {
                $actions = $et->getActionsFor("WBD");
                try {
                    $wbd = $actions->select();
                } catch (\Exception $e) {
                    continue;
                }

                $wbd->update();
            }

            $cfg_idd = $et->getConfigIDD();
            if ($cfg_idd !== null && $cfg_idd->getAvailable()) {
                $actions = $et->getActionsFor("IDD");
                try {
                    $idd = $actions->select();
                } catch (\Exception $e) {
                    continue;
                }

                $idd->update();
            }

            $cfg_gti = $et->getConfigGTI();
            if ($cfg_gti !== null && $cfg_gti->getAvailable()) {
                $actions = $et->getActionsFor("GTI");
                try {
                    $idd = $actions->select();
                } catch (\Exception $e) {
                    continue;
                }

                $idd->update();
            }
        }
    }

    protected function historizeCourseClassificationData()
    {
        foreach ($this->getCCReferences() as $ref_id) {
            $cc = new ilObjCourseClassification($ref_id);
            $cc->update();
        }
    }

    protected function getSessionReferences()
    {
        $aux = array_map(
            function ($rec) {
                return $rec['obj_id'];
            },
            ilObject::_getObjectsByType('sess')
        );
        $return = [];
        foreach ($aux as $cc_id) {
            $ref_ids = \ilObject::_getAllReferences($cc_id);
            $return = array_merge($return, $ref_ids);
        }
        return array_unique($return);
    }



    protected function getCCReferences()
    {
        $aux = array_map(
            function ($rec) {
                return $rec['obj_id'];
            },
            ilObject::_getObjectsByType('xccl')
        );
        $return = [];
        foreach ($aux as $cc_id) {
            $ref_ids = \ilObject::_getAllReferences($cc_id);
            $return = array_merge($return, $ref_ids);
        }
        return array_unique($return);
    }

    protected function getCMReferences()
    {
        $aux = array_map(
            function ($rec) {
                return $rec['obj_id'];
            },
            ilObject::_getObjectsByType('xcmb')
        );
        $return = [];
        foreach ($aux as $cm_id) {
            $ref_ids = \ilObject::_getAllReferences($cm_id);
            $return = array_merge($return, $ref_ids);
        }
        return array_unique($return);
    }

    protected function getETReferences()
    {
        $aux = array_map(
            function ($rec) {
                return $rec['obj_id'];
            },
            ilObject::_getObjectsByType('xetr')
        );
        $return = [];
        foreach ($aux as $cm_id) {
            $ref_ids = \ilObject::_getAllReferences($cm_id);
            $return = array_merge($return, $ref_ids);
        }
        return array_unique($return);
    }
}
