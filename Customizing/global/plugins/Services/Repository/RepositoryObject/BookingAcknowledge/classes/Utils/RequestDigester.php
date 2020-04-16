<?php

namespace CaT\Plugins\BookingAcknowledge\Utils;

/**
 *
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class RequestDigester
{
    const CONCAT_CHAR = '_';
    const F_USERCOURSE = 'f_usrcrs';
    const F_FILTER = 'filter_params';
    const CMD_ACKNOWLEDGE = "acknowledge";
    const CMD_DECLINE = "decline";
    const CMD_ACKNOWLEDGE_CONFIRM = "acknowledge_confirm";
    const CMD_DECLINE_CONFIRM = "decline_confirm";
    const CMD_MULTI_ACTION = "multi_command";
    const CMD_MAIL = "c_mail";

    /**
     * Get values from request (both POST and GET)
     */
    public function digest() : array
    {
        $get = $_GET[self::F_USERCOURSE];
        $get = $this->validate($get);

        if (count($get) > 0) {
            $post = [];
        } else {
            $post = $_POST[self::F_USERCOURSE];
            $post = $this->validate($post);
        }

        $usrcrs_params = array_merge($get, $post);
        $ret = [];

        foreach ($usrcrs_params as $usrcrs) {
            list($usr_id, $crs_ref_id) = array_map('intval', explode(self::CONCAT_CHAR, $usrcrs));

            $ret[] = new UserCourseRelation(
                $usr_id,
                $crs_ref_id
            );
        }

        return $ret;
    }

    public function validate($params) : array
    {
        $ret = [];
        if (!is_array($params)) {
            $params = [$params];
        }
        foreach ($params as $value) {
            if (is_null($value)) {
                continue;
            }
            list($usr_id, $crs_ref_id) = explode(self::CONCAT_CHAR, $value);
            if (is_numeric($usr_id) && is_numeric($crs_ref_id)) {
                $ret[] = $value;
            }
        }

        return $ret;
    }

    /**
     * Prepare a value for user-course-relation
     */
    public function prepare(int $usr_id, int $crs_ref_id) : string
    {
        return implode(self::CONCAT_CHAR, [$usr_id, $crs_ref_id]);
    }

    public function getUsrCrsParameter() : string
    {
        return self::F_USERCOURSE;
    }

    public function getCmdAcknowledge() : string
    {
        return self::CMD_ACKNOWLEDGE;
    }
    public function getCmdAcknowledgeConfirm() : string
    {
        return self::CMD_ACKNOWLEDGE_CONFIRM;
    }

    public function getCmdDecline() : string
    {
        return self::CMD_DECLINE;
    }

    public function getCmdDeclineConfirm() : string
    {
        return self::CMD_DECLINE_CONFIRM;
    }

    public function getCmdMail() : string
    {
        return self::CMD_MAIL;
    }

    public function getFilterSettingsFromRequest() : array
    {
        return [
            self::F_FILTER,
            $_GET[self::F_FILTER]
        ];
    }
}
