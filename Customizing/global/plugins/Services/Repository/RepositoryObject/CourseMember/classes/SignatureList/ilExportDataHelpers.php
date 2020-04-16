<?php
namespace CaT\Plugins\CourseMember\SignatureList;

trait ilExportDataHelpers
{

    /**
     * Get Orgunit of user
     *
     * @param int 	$user_id
     * @return string
     */
    protected function getOrgUnitOf($user_id)
    {
        return \ilObjUser::lookupOrgUnitsRepresentation($user_id);
    }

    /**
     * get venue of course
     *
     * @return string
     */
    protected function getVenueOfCourse()
    {
        require_once("Services/Component/classes/class.ilPluginAdmin.php");
        if (\ilPluginAdmin::isPluginActive('venues')) {
            $vplug = \ilPluginAdmin::getPluginObjectById('venues');
            list($venue_id, $city, $address, $name, $postcode) = $vplug->getVenueInfos($this->course->getId());

            if ($name != "") {
                $ext[] = $name;
            }
            if ($city != "") {
                $ext[] = $city;
            }

            if (count($ext) == 2) {
                $t = join(", ", $ext);
            } elseif (count($ext) == 1) {
                $t = $ext[0];
            }
            return $t;
        }
        return '';
    }

    /**
     * get all trainers (tutors) of course
     *
     * @param 	ilObjCourse 	$course
     * @return 	string
     */
    protected function getTrainersOfCourse($course)
    {
        $trainer = array();

        foreach ($course->getMembersObject()->getTutors() as $key => $trainer_id) {
            $trainer[] = sprintf(
                "%s",
                \ilObjUser::_lookupFullname($trainer_id)
            );
        }
        return implode(", ", $trainer);
    }
}
