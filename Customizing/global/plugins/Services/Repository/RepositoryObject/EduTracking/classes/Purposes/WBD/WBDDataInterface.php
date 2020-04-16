<?php

namespace CaT\Plugins\EduTracking\Purposes\WBD;

class WBDDataInterface
{
    const NONE_USER = 'none_user';

    public function __construct(WBD $wbd, Configuration\ConfigWBD $config_wbd, WBDUserDataProvider $udp, WBDObjectProvider $op)
    {
        $this->wbd = $wbd;
        $this->config_wbd = $config_wbd;
        $this->parent_crs = $wbd->getObject()->getParentCourse();
        $this->udp = $udp;
        $this->op = $op;
        $this->loadContactInfos();
    }

    protected function loadContactInfos()
    {
        $contact = $this->config_wbd->getContact();

        switch ($contact) {
            case Configuration\ConfigWBD::M_FIX_CONTACT:
                $this->loadUserInfos($this->config_wbd->getUserId());
                break;
            case Configuration\ConfigWBD::M_COURSE_TUTOR:
                $this->loadCourseTutorInfo();
                break;
            case Configuration\ConfigWBD::M_COURSE_ADMIN:
                $this->loadCourseAdminInfo();
                break;
            case Configuration\ConfigWBD::M_XCCL_CONTACT:
                $this->loadContactFromXCCL();
                break;
        }
    }



    /**
     * Get contact informations of ILIAS user
     *
     * @param int 	$user_id
     *
     * @return void
     */
    protected function loadUserInfos($user_id)
    {
        list($title, $firstname, $lastname, $phone, $mail) = $this->udp->getUserInformation($user_id);
        $this->contact_provided = false;
        if ($firstname !== '' || $lastname !== '' || $mail !== '' || $phone !== '') {
            $this->contact_provided = true;
        }
        $this->c_title = $title;
        $this->c_firstname = $firstname;
        $this->c_lastname = $lastname;
        $this->c_phone = $phone;
        $this->c_mail = $mail;
    }

    /**
     * Get contact informations of first course tutor
     *
     * @param int 	$user_id
     *
     * @return void
     */
    protected function loadCourseTutorInfo()
    {
        if ($this->parent_crs !== null) {
            $tutors = $this->parent_crs->getMembersObject()->getTutors();
            if (count($tutors) > 0) {
                $this->loadUserInfos(array_shift($tutors));
                return;
            }
        }
        $this->contact_provided = false;
    }

    /**
     * Get contact informations of first course admin
     *
     * @param int 	$user_id
     *
     * @return void
     */
    protected function loadCourseAdminInfo()
    {
        if ($this->parent_crs !== null) {
            $admins = $this->parent_crs->getMembersObject()->getAdmins();
            if (count($admins) > 0) {
                $this->loadUserInfos(array_shift($admins));
                return;
            }
        }
        $this->contact_provided = false;
    }

    /**
     * Get contact information from xccl object if existing
     *
     * @return void
     */
    protected function loadContactFromXCCL()
    {
        $this->c_title = "";
        $this->c_firstname = "";
        if ($this->parent_crs !== null) {
            $xccl = $this->op->getFirstChildOfByType($this->parent_crs->getRefId(), "xccl");
            if ($xccl !== null) {
                $this->contact_provided = true;
                $contact = $xccl->getCourseClassification()->getContact();
                $this->c_lastname = $contact->getName();
                $this->c_phone = $contact->getPhone();
                $this->c_mail = $contact->getMail();
                return;
            }
        }
        $this->contact_provided = false;
        $this->c_lastname = "";
        $this->c_phone = "";
        $this->c_mail = "";
    }

    /**
     * @return string
     */
    public function getEducationType()
    {
        return $this->wbd->getEducationType();
    }

    /**
     * @return string
     */
    public function getEducationContent()
    {
        return $this->wbd->getEducationContent();
    }

    /**
     * @return string
     */
    public function getCourseTitle()
    {
        if ($this->parent_crs === null) {
            return null;
        }
        return $this->parent_crs->getTitle();
    }

    /**
     * @return \DateTime|null
     */
    public function getStartDate()
    {
        if ($this->parent_crs === null) {
            return null;
        }
        $crs_start = $this->parent_crs->getCourseStart();
        if ($crs_start === null) {
            return null;
        }
        $dt = new \DateTime();
        $dt->setTimestamp((int) $crs_start->getUnixTime());
        return $dt;
    }


    /**
     * @return \DateTime|null
     */
    public function getEndDate()
    {
        if ($this->parent_crs === null) {
            return null;
        }
        $crs_end = $this->parent_crs->getCourseEnd();
        if ($crs_end === null) {
            return null;
        }
        $dt = new \DateTime();
        $dt->setTimestamp((int) $crs_end->getUnixTime());
        return $dt;
    }


    /**
     * @return string|null
     */
    public function getInternalId()
    {
        return 'cat-tms-' . CLIENT_ID . '-{USR_ID}-{REF_ID}';
    }

    /**
     * @return bool;
     */
    public function contactProvided()
    {
        return $this->contact_provided;
    }

    /**
     * @return string|null
     */
    public function getContactType()
    {
        return $this->config_wbd->getContact();
    }

    /**
     * @return string|null
     */
    public function getContactTitle()
    {
        return $this->c_title;
    }

    /**
     * @return string|null
     */
    public function getContactFirstname()
    {
        return $this->c_firstname;
    }

    /**
     * @return string|null
     */
    public function getContactLastname()
    {
        return $this->c_lastname;
    }

    /**
     * @return string|null
     */
    public function getContactPhone()
    {
        return $this->c_phone;
    }

    /**
     * @return string|null
     */
    public function getContactEmail()
    {
        return $this->c_mail;
    }
}
