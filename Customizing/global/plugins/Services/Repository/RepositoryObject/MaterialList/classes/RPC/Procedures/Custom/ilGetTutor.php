<?php

namespace CaT\Plugins\MaterialList\RPC\Procedures\Custom;

use CaT\Plugins\MaterialList\RPC;

require_once("Services/User/classes/class.ilObjUser.php");

/**
 * Procedure to get information of tutors
 */
class ilGetTutor extends RPC\ilFunctionBase
{
    const COLUMN_TITLE = "crs_tutor";

    /**
     * Return the title of crs
     *
     * @return string
     */
    public function run()
    {
        global $DIC;
        $g_lng = $DIC->language();
        $g_lng->loadLanguageModule("crs");
        $ret = array();
        foreach ($this->crs->getMembersObject()->getTutors() as $key => $tutor) {
            $user = new \ilObjUser($tutor);
            $ret[] = $user->getFirstname() . " " . $user->getLastname() . " <" . $user->getEmail() . ">";
        };

        return new RPC\FunctionResult($g_lng->txt(self::COLUMN_TITLE), implode(", ", $ret));
    }
}
