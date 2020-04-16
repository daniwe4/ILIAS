<?php

namespace CaT\Plugins\UserCourseHistorizing;

class RehistorizeLocalRoles
{
    /**
     * @var \ilDBInterface
     */
    protected $db;

    /**
     * @var \ilAppEventHandler
     */
    protected $event;

    public function __construct(\ilDBInterface $db, \ilAppEventHandler $event)
    {
        $this->db = $db;
        $this->event = $event;
    }

    public function run()
    {
        foreach ($this->getCourses() as $crs) {
            $this->historizeLocalRoleFor($crs);
        }
    }

    /**
     * Get all future courses from history
     *
     * @return \ilObjCourse[]
     */
    protected function getCourses()
    {
        $query = "SELECT oref.ref_id" . PHP_EOL
            . " FROM hhd_crs hhd" . PHP_EOL
            . " JOIN object_reference oref" . PHP_EOL
            . "     ON oref.obj_id = hhd.crs_id" . PHP_EOL
            . " WHERE (hhd.deleted = 0 OR hhd.deleted IS NULL)" . PHP_EOL
            . "     AND oref.deleted IS NULL"
        ;

        $res = $this->db->query($query);

        $ret = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $ret[] = \ilObjectFactory::getInstanceByRefId($row["ref_id"]);
        }
        return $ret;
    }

    public function historizeLocalRoleFor(\ilObjCourse $crs)
    {
        $payload = [
            "obj_id" => (int) $crs->getId()
        ];
        foreach ($crs->getMembersObject()->getParticipants() as $participant) {
            $payload["usr_id"] = (int) $participant;
            $this->event->raise(
                "Modules/Course",
                "historizeLocalRoles",
                $payload
            );
        }
    }
}
