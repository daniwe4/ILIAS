<?php

require_once __DIR__ . '/../libs/vendor/autoload.php';
require_once 'Services/Cron/classes/class.ilCronHookPlugin.php';

use CaT\Historization as Hist;
use CaT\Historization\HistCase\HistCase;
use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistCourse;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistUserCourse;
use CaT\Plugins\UserCourseHistorizing\HistCases\HistSessionCourse;
use CaT\Plugins\UserCourseHistorizing\HistorizingFromBufferJob as Job;
use CaT\Plugins\UserCourseHistorizing\DI;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilUserCourseHistorizingPlugin extends ilCronHookPlugin
{
    use DI;

    /**
     * @var Pimple\Container
     */
    protected $dic;

    public function __construct()
    {
        parent::__construct();
        global $DIC;
        $this->dic = $this->buildDIC($DIC);
    }

    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "UserCourseHistorizing";
    }


    public function getCronJobInstances()
    {
        return [$this->dic["job"]];
    }

    public function getCronJobInstance($a_job_id)
    {
        global $DIC;
        return $this->dic["job"];
    }

    public function handleEvent($component, $event, $parameter)
    {
        if ($this->isSerialEvent($component, $event)) {
            foreach ($this->splitEventByElements($component, $event, $parameter) as $parameter_single) {
                $this->buffer(new Hist\Event\Event($event, $component, $parameter_single));
            }
        } elseif ($this->isJointEvent($component, $event)) {
            $this->buffer(new Hist\Event\Event($event, $component, $parameter));
            foreach ($this->splitEventByEvent($component, $event, $parameter) as $evt_obj) {
                $this->buffer($evt_obj);
            }
        } elseif ($this->isRemoveEvent($component, $event)) {
            $this->remove((int) $parameter["crs_id"], (int) $parameter["usr_id"]);
        } else {
            $this->buffer(new Hist\Event\Event($event, $component, $parameter));
        }
    }

    /**
     * These events may affect several cases
     */
    private function isSerialEvent($component, $event)
    {
        return 	($component === 'Plugin/CourseClassification'
                    && ($event === 'updateCCObject' || $event === 'updateCCStatic'))
            || ($component === 'Plugin/Venue' && $event === 'updateVenueStatic')
            || ($component === 'Plugin/TrainingProvider' && $event === 'updateTrainingProviderStatic')
            || ($component === 'Plugin/Accounting' && $event === 'updateVatRate')
            || ($component === 'Plugin/EduTracking' && $event === 'updateIDD');
    }

    /**
     * These events may be split into several events affecting one or more cases.
     */
    private function isJointEvent($component, $event)
    {
        return $component === 'Services/Tree' && $event === 'moveTree';
    }

    private function isRemoveEvent($component, $event)
    {
        return $component === 'Plugin/WBDInterface' && $event === 'stornoParticipationWBD';
    }

    private function splitEventByElements($component, $event, array $parameter)
    {
        assert('is_string($event)');
        assert('is_string($component)');
        return $this->getRelevantCases($component, $event, $parameter);
    }

    private function splitEventByEvent($component, $event, array $parameter)
    {
        $return = [];
        if ($component === 'Services/Tree' && $event === 'moveTree') {
            $return[] = new Hist\Event\Event(
                'delete',
                'Services/Object',
                ['node_id' => $parameter['source_id']
                ,'old_parent_ref_id' => $parameter['old_parent_id']
                ,'type' => \ilObject::_lookupType($parameter['source_id'], true)
                ]
            );
            $return[] = new Hist\Event\Event(
                'insertNode',
                'Services/Tree',
                ['node_id' => $parameter['source_id']
                ,'parent_id' => $parameter['target_id']]
            );
        }
        return $return;
    }

    private function getRelevantCases($component, $event, array $parameter)
    {
        assert('is_string($event)');
        assert('is_string($component)');
        $return = [];
        global $DIC;
        $tree = $DIC['tree'];
        if ($component === 'Plugin/CourseClassification') {
            if ($event === 'updateCCObject' || $event === 'updateCCStatic') {
                foreach ($parameter['cc_obj_ids'] as $obj_id) {
                    foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
                        foreach ($tree->getNodePath($ref_id) as $node) {
                            if ($node['type'] === 'crs') {
                                $return[] = ['crs_id' => (int) $node['obj_id'], 'cc_ref_id' => (int) $ref_id];
                                break;
                            }
                        }
                    }
                }
            }
        }
        if ($component === 'Plugin/EduTracking') {
            if ($event === 'updateIDD') {
                $obj_id = $parameter['xetr_obj_id'];
                foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
                    foreach ($tree->getNodePath($ref_id) as $node) {
                        if ($node['type'] === 'crs') {
                            $return[] = array_merge(
                                ['crs_id' => (int) $node['obj_id']],
                                $parameter
                            );
                            break;
                        }
                    }
                }
            }
        }
        if (($component === 'Plugin/Venue' && $event === 'updateVenueStatic') ||
            ($component === 'Plugin/TrainingProvider' && $event === 'updateTrainingProviderStatic')) {
            foreach ($parameter['crs_obj_ids'] as $obj_id) {
                $return[] = ['crs_id' => $obj_id];
            }
        }

        if ($component === 'Plugin/Accounting' && $event === 'updateVatRate') {
            foreach ($parameter['xaccs'] as $acc) {
                $return[] = ['xacc' => $acc];
            }
        }
        return $return;
    }

    public function buffer(Hist\Event\Event $evt)
    {
        $buffer_access = $this->dic["buffer.access"];
        foreach ($this->dic["cases"] as $case) {
            if ($case->isEventRelevant($evt)) {
                $evt = $case->addDigesters($evt);
                $buffer_access->withHistCase($case)->buffer($evt);
            }
        }
    }

    public function remove(int $crs_id, int $usr_id)
    {
        $buffer_storage = $this->dic["buffer_storage.user_course"];
        $buffer_storage->delete(["crs_id" => $crs_id, "usr_id" => $usr_id]);
    }

    public function getHistUserCourseCase() : HistUserCourse
    {
        return $this->dic["case.user_course"];
    }
}
