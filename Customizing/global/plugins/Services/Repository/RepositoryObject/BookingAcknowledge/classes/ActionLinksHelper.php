<?php

namespace CaT\Plugins\BookingAcknowledge;

use CaT\Plugins\BookingAcknowledge\Utils\RequestDigester;
use CaT\Plugins\BookingAcknowledge\Utils\CourseInfo;
use CaT\Plugins\BookingAcknowledge\Utils\UserInfo;
use CaT\Plugins\BookingAcknowledge\Utils\AccessHelper;

/**
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 * @author Denis Klöpfer 	<denis.kloepfer@concepts-and-training.de>
 * @author Nils Haagen 	<nils.haagen@concepts-and-training.de>
 */
class ActionLinksHelper
{
    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var \ArrayAccess
     */
    protected $dic;

    /**
     * @var int
     */
    protected $acting_usr_id;

    /**
     * @var RequestDigester
     */
    protected $digester;

    /**
     * @var CourseInfo
     */
    protected $crs_info;

    /**
     * @var UserInfo
     */
    protected $usr_info;

    /**
     * @var AccessHelper
     */
    protected $access_helper;

    public function __construct(
        \ArrayAccess $dic,
        \ilCtrl $ctrl,
        int $acting_usr_id,
        RequestDigester $digester,
        CourseInfo $course_info,
        UserInfo $user_info,
        AccessHelper $access
    ) {
        $this->dic = $dic;
        $this->ctrl = $ctrl;
        $this->acting_usr_id = $acting_usr_id;
        $this->digester = $digester;
        $this->crs_info = $course_info;
        $this->usr_info = $user_info;
        $this->access_helper = $access;
    }

    /**
     * Get the dictionary object
     *
     * @return Object
     */
    protected function getDIC()
    {
        return $this->dic;
    }


    public function withRefId(int $crs_ref_id) : ActionLinksHelper
    {
        $other = clone $this;
        $other->crs_ref_id = $crs_ref_id;
        return $other;
    }

    public function getRefId() : int
    {
        return $this->crs_ref_id;
    }

    public function withUsrId(int $usr_id) : ActionLinksHelper
    {
        $other = clone $this;
        $other->usr_id = $usr_id;
        return $other;
    }

    public function getUsrId() : int
    {
        return $this->usr_id;
    }

    public function getLinkToTraining() : string
    {
        if (!$this->getRefId()) {
            throw new \Exception('Undefined entity ref id');
        }
        $cmd = 'showSummary';
        $this->ctrl->setParameterByClass('ilObjCourseGUI', 'ref_id', $this->getRefId());
        $link = $this->ctrl->getLinkTargetByClass(['ilRepositoryGUI','ilObjCourseGUI', 'ilInfoScreenGUI'], $cmd);
        return $link;
    }

    public function getEntryActions() : array
    {
        $ret = [
            'mail' => $this->getMailtoLink()
        ];
        if ($this->access_helper->mayAcknowledge()) {
            $ret['acknowledge'] = $this->getLink($this->digester->getCmdAcknowledgeConfirm());
            $ret['decline'] = $this->getLink($this->digester->getCmdDeclineConfirm());
        }
        return $ret;
    }

    public function getLink(string $cmd) : string
    {
        $cls = 'ilObjBookingAcknowledgeGUI';
        $param = $this->digester->getUsrCrsParameter();
        $value = $this->digester->prepare($this->getUsrId(), $this->getRefId());

        $this->ctrl->setParameterByClass($cls, $param, $value);

        if (in_array($cmd, [
            $this->digester->getCmdAcknowledgeConfirm(),
            $this->digester->getCmdDeclineConfirm()
        ])) {
            list($param, $value) = $this->digester->getFilterSettingsFromRequest();
            $this->ctrl->setParameterByClass($cls, $param, $value);
        }

        $link = $this->ctrl->getLinkTargetByClass($cls, $cmd);
        $this->ctrl->setParameterByClass($cls, $param, null);

        return $link;
    }

    public function getMailtoLink() : string
    {
        $crs_info = $this->crs_info->withRefId($this->getRefId());
        $crs_admins = $crs_info->getAdminIds();

        $mails = [];
        foreach ($crs_admins as $id) {
            $user_info = $this->usr_info->withId($id);
            $mails[] = $user_info->getEmail();
        }

        return 'mailto:' . implode(',', $mails);
    }

    protected function getCourseInfo(int $crs_ref_id)
    {
        if (!array_key_exists($crs_ref_id, $this->crs_infos)) {
            $this->crs_infos[$crs_ref_id] = $this->crs_info->withRefId($crs_ref_id);
        }
        return $this->crs_infos[$crs_ref_id];
    }
}
