<?php

namespace CaT\Plugins\WBDCrsHistorizing\Digesters;

use CaT\Historization\Digester\Digester as Digester;

class CrsRoleAssignmentDigester implements Digester
{
    public function digest(array $payload)
    {
        global $DIC;
        $rbac_review = $DIC['rbacreview'];
        $rbac_review->clearCaches();
        $role_title = \ilObject::_lookupTitle($payload['role_id']);
        $usr_id = array_shift($rbac_review->assignedUsers($payload['role_id']));

        if (!$usr_id) {
            if (strpos($role_title, 'il_crs_tutor_') === 0) {
                return [
                            'contact_title_tutor' => ''
                            ,'contact_firstname_tutor' => ''
                            ,'contact_lastname_tutor' => ''
                            ,'contact_email_tutor' => ''
                            ,'contact_phone_tutor' => ''
                        ];
            } elseif (strpos($role_title, 'il_crs_admin_') === 0) {
                return [
                            'contact_title_admin' => ''
                            ,'contact_firstname_admin' => ''
                            ,'contact_lastname_admin' => ''
                            ,'contact_email_admin' => ''
                            ,'contact_phone_admin' => ''
                        ];
            }
            return [];
        }
        $usr = new \ilObjUser($usr_id);

        $mail = $usr->getEmail();
        $title = $usr->getUTitle();
        $firstname = $usr->getFirstname();
        $lastname = $usr->getLastname();
        $phone = $usr->getPhoneOffice();


        $return = [];
        if (strpos($role_title, 'il_crs_tutor_') === 0) {
            $return['contact_title_tutor'] = $title;
            $return['contact_firstname_tutor'] = $firstname;
            $return['contact_lastname_tutor'] = $lastname;
            $return['contact_email_tutor'] = $mail;
            $return['contact_phone_tutor'] = $phone;
        } elseif (strpos($role_title, 'il_crs_admin_') === 0) {
            $return['contact_title_admin'] = $title;
            $return['contact_firstname_admin'] = $firstname;
            $return['contact_lastname_admin'] = $lastname;
            $return['contact_email_admin'] = $mail;
            $return['contact_phone_admin'] = $phone;
        }
        return $return;
    }
}
