<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Services/Cron/classes/class.ilCronHookPlugin.php';

use CaT\Historization as Hist;
use CaT\Historization\Persistence\Mysql as Mysql;
use CaT\Historization\ILIAS as ILIAS;
use CaT\Plugins\WBDCrsHistorizing\HistCases\WBDCrs;
use CaT\Plugins\WBDCrsHistorizing\HistorizingFromBufferJob as Job;

/**
 * Plugin base class. Keeps all information the plugin needs
 */
class ilWBDCrsHistorizingPlugin extends ilCronHookPlugin
{
    /**
     * Get the name of the Plugin
     *
     * @return string
     */
    public function getPluginName()
    {
        return "WBDCrsHistorizing";
    }

    public function getCronJobInstances()
    {
        global $DIC;
        return [new Job($DIC['ilDB'], $this->cases())];
    }

    public function getCronJobInstance($a_job_id)
    {
        global $DIC;
        return new Job($DIC['ilDB'], $this->cases());
    }


    public function handleEvent($component, $event, $parameter)
    {
        if ($this->serialEvent($component, $event)) {
            foreach ($this->splitEventByElements($component, $event, $parameter) as $parameter_single) {
                $this->buffer(new Hist\Event\Event($event, $component, $parameter_single));
            }
        } elseif ($this->jointEvent($component, $event)) {
            foreach ($this->splitEventByEvent($component, $event, $parameter) as $evt_obj) {
                $this->buffer($evt_obj);
            }
        } else {
            $this->buffer(new Hist\Event\Event($event, $component, $parameter));
        }
    }

    public function buffer(Hist\Event\Event $evt)
    {
        global $DIC;
        $buffer_access = new Hist\BufferAccess(new Mysql\MysqlBufferFactory(new ILIAS\IliasSql($DIC['ilDB'])));
        foreach ($this->cases() as $case) {
            if ($case->isEventRelevant($evt)) {
                $evt = $case->addDigesters($evt);
                $buffer_access->withHistCase($case)->buffer($evt);
            }
        }
    }

    /**
     * These events may be split into several events affecting one or more cases.
     */
    private function jointEvent($component, $event)
    {
        return $component === 'Services/Tree' && $event === 'moveTree';
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
                ,
                'parent_id' => $parameter['target_id']]
            );
        }
        return $return;
    }
    /**
     * These events may affect several cases
     */
    private function serialEvent($component, $event)
    {
        return	($component === 'Plugin/CourseClassification' && $event === 'updateCCObject')
            || ($component === 'Plugin/EduTracking' && $event === 'updateWBD');
    }

    private function splitEventByElements($component, $event, array $parameter)
    {
        assert('is_string($event)');
        assert('is_string($component)');
        return $this->getRelevantCases($component, $event, $parameter);
    }

    private function getRelevantCases($component, $event, array $parameter)
    {
        assert('is_string($event)');
        assert('is_string($component)');
        $return = [];
        global $DIC;
        $tree = $DIC['tree'];
        if ($component === 'Plugin/CourseClassification') {
            if ($event === 'updateCCObject') {
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
            if ($event === 'updateWBD') {
                $obj_id = $parameter['xetr_obj_id'];
                foreach (ilObject::_getAllReferences($obj_id) as $ref_id) {
                    foreach ($tree->getNodePath($ref_id) as $node) {
                        if ($node['type'] === 'crs') {
                            $aux = array_merge(
                                ['crs_id' => (int) $node['obj_id']],
                                $parameter
                            );
                            $aux['internal_id'] = str_replace('{REF_ID}', $node['child'], $aux['internal_id']);
                            $return[] = $aux;
                            break;
                        }
                    }
                }
            }
        }
        return $return;
    }

    protected function cases()
    {
        return [new WBDCrs()];
    }
}
