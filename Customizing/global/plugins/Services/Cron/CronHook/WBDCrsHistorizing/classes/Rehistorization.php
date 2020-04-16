<?php declare(strict_types = 1);



class Rehistorization
{
    public function __construct()
    {
        $this->plugin = new ilWBDCrsHistorizingPlugin();
    }

    public function rehistorizeAll()
    {
        $this->rehistorizeCCl();
        $this->rehistorizeETR();
        $this->rehistorizeAssignments();
    }

    public function rehistorizeCCL()
    {
        $to_update = [];
        foreach ($this->getAllObjectsOfType('xccl') as $ccl) {
            $to_update[] = $ccl->getId();
        }
        $payload = ['cc_obj_ids' => $to_update];
        $this->plugin->handleEvent(
            'Plugin/CourseClassification',
            'updateCCObject',
            $payload
        );
    }

    public function rehistorizeETR()
    {
        foreach ($this->getAllObjectsOfType('xetr') as $etr) {
            $payload = [];
            try {
                $di = $etr->getWBDDataInterface();
            } catch (Exception $e) {
                continue;
            }
            $payload['xetr_obj_id'] = $etr->getId();
            $payload['internal_id'] = $di->getInternalId();
            $payload['wbd_learning_type'] = $di->getEducationType();
            $payload['wbd_learning_content'] = $di->getEducationContent();
            $this->plugin->handleEvent(
                'Plugin/EduTracking',
                'updateWBD',
                $payload
            );
        }
    }

    public function rehistorizeAssignments()
    {
        foreach ($this->getAllObjectsOfType('crs') as $crs) {
            $tutor_role_id = $crs->getDefaultTutorRole();
            $admin_role_id = $crs->getDefaultAdminRole();
            $this->plugin->handleEvent('Services/AccessControl', 'deassignUser', [
                    'type' => 'crs'
                    ,'role_id' => $tutor_role_id
                    ,'crs_id' => $crs->getId()
                ]);
            $this->plugin->handleEvent('Services/AccessControl', 'deassignUser', [
                    'type' => 'crs'
                    ,'role_id' => $admin_role_id
                    ,'crs_id' => $crs->getId()
                ]);
        }
    }

    public function getAllObjectsOfType(string $type)
    {
        $aux = array_map(
            function ($rec) {
                return $rec['obj_id'];
            },
            ilObject::_getObjectsByType($type)
        );
        $return_ids = [];
        foreach ($aux as $cm_id) {
            $ref_ids = \ilObject::_getAllReferences($cm_id);
            $return_ids = array_merge($return_ids, $ref_ids);
        }
        $return_ids = array_unique($return_ids);
        $return = [];
        foreach ($return_ids as $ref_id) {
            $return[] = ilObjectFactory::getInstanceByRefId($ref_id);
        }
        return $return;
    }
}
