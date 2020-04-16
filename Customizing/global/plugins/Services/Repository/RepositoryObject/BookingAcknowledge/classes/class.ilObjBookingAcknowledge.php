<?php

declare(strict_types=1);

use CaT\Ente\ILIAS\ilProviderObjectHelper;
use CaT\Plugins\BookingAcknowledge;
use CaT\Plugins\BookingAcknowledge\Settings;
use CaT\Plugins\BookingAcknowledge\Utils\AccessHelper;
use CaT\Plugins\BookingAcknowledge\Utils\RequestDigester;
use CaT\Plugins\BookingAcknowledge\Utils\CourseInfo;
use CaT\Plugins\BookingAcknowledge\Utils\UserInfo;
use CaT\Plugins\BookingAcknowledge\Acknowledgments\Acknowledgment;
use CaT\Plugins\BookingAcknowledge\Mailing\MailFactory;
use CaT\Plugins\BookingAcknowledge\Acknowledgments\AcknowledgmentGUI;

/**
 * Object of the plugin.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 * @copyright Extended GPL, see LICENSE
 */
class ilObjBookingAcknowledge extends ilObjectPlugin implements BookingAcknowledge\BookingAcknowledge
{
    use ilProviderObjectHelper;
    use BookingAcknowledge\DI;

    /**
     * @var array <int ref_id => CourseInfo>
     */
    protected $crs_infos = [];

    /**
     * @var CourseInfo
     */
    protected $crs_info;

    /**
     * @var array <int ref_id => UserInfo>
     */
    protected $usr_infos = [];

    /**
     * @var UserInfo
     */
    protected $usr_info;

    /**
     * @var AccessHelper
     */
    protected $access;


    public function __construct(int $ref_id = 0)
    {
        parent::__construct($ref_id);

        global $DIC;
        $this->dic = $DIC;
        $this->g_ctrl = $DIC["ilCtrl"];
    }

    protected function getAccessHelper()
    {
        if (!$this->access) {
            $this->access = new AccessHelper($this->dic["ilAccess"], (int) $this->getRefId());
        }
        return $this->access;
    }

    public function doCreate()
    {
        $this->createUnboundProvider("root", BookingAcknowledge\UnboundProvider::class, __DIR__ . "/UnboundProvider.php");
    }

    public function doCloneObject($new_obj, $a_target_id, $a_copy_id = null)
    {
    }

    public function doDelete()
    {
    }

    /**
     * Get called after object creation to read further information.
     */
    public function doRead()
    {
    }

    public function doUpdate()
    {
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function updateSettings(\Closure $c)
    {
        $this->settings = $c($this->settings);
    }

    /**
     * Init the type of the plugin. Same value as chosen in plugin.php
     */
    public function initType()
    {
        $this->setType(BookingAcknowledge\BookingAcknowledge::PLUGIN_ID);
    }

    /**
     * Get a closure to get txts from plugin.
     *
     * @return \Closure
     */
    public function txtClosure()
    {
        return function ($code) {
            return $this->txt($code);
        };
    }

    public function getDirectory()
    {
        return $this->plugin->getDirectory();
    }

    protected function getIconPath() : string
    {
        return $this->getPlugin()->getImagePath("icon_xack.svg");
    }

    protected function getActiceIconPath() : string
    {
        return $this->getPlugin()->getImagePath("icon_xack.svg");
    }

    public function getTxtClosure() : Closure
    {
        return function ($code) {
            return $this->plugin->txt($code);
        };
    }

    /**
     * @inheritdoc
     */
    protected function getDIC()
    {
        return $this->dic;
    }

    public function getDI()
    {
        if (is_null($this->di)) {
            $this->di = $this->getObjectDI(
                $this,
                $this->getDIC(),
                $this->getTxtClosure()
            )
            ;
        }
        return $this->di;
    }

    protected function getAcknowledgeDB()
    {
        return $this->getDI()["db.acknowledge"];
    }

    /**
     * @param UserCourseRelation[] $entries
     */
    public function acknowledge(array $entries)
    {
        $state = Acknowledgment::APPROVED;
        $this->createAcknowledgments($state, $entries);
    }

    /**
     * @param UserCourseRelation[] $entries
     */
    public function decline(array $entries)
    {
        $state = Acknowledgment::DECLINED;
        $this->createAcknowledgments($state, $entries);
        $this->sendDeclineMailToCourseAdmins($entries);
    }

    /**
     * @param int $state
     * @param UserCourseRelation[] $entries
     */
    protected function createAcknowledgments(int $state, array $entries)
    {
        $acting_user = (int) $this->getDIC()['ilUser']->getId();
        $db = $this->getDI()['db.acknowledge'];

        foreach ($entries as $entry) {
            $db->create(
                $acting_user,
                $entry->getUserId(),
                $entry->getCourseRefId(),
                $state
            );
        }
    }


    public function getCourseInfo(int $crs_ref_id) : CourseInfo
    {
        if (!$this->crs_info) {
            $this->crs_info = $this->getDI()['info.course'];
        }
        if (!array_key_exists($crs_ref_id, $this->crs_infos)) {
            $this->crs_infos[$crs_ref_id] = $this->crs_info->withRefId($crs_ref_id);
        }
        return $this->crs_infos[$crs_ref_id];
    }

    public function getUserInfo(int $usr_id) : UserInfo
    {
        if (!$this->usr_info) {
            $this->usr_info = $this->getDI()['info.user'];
        }
        if (!array_key_exists($usr_id, $this->usr_infos)) {
            $this->usr_infos[$usr_id] = $this->usr_info->withId($usr_id);
        }
        return $this->usr_infos[$usr_id];
    }


    /**
     * @param UserCourseRelation[] $entries
     */
    public function sendDeclineMailToCourseAdmins(array $entries)
    {
        $mailer = $this->getDI()['mailer'];

        $mails = [];
        foreach ($entries as $entry) {
            $crs_info = $this->getCourseInfo($entry->getCourseRefId());
            $course_admins = $crs_info->getAdminIds();
            $mails = array_merge(
                $mails,
                $mailer->getMails(
                    $course_admins,
                    $entry->getCourseRefId(),
                    $entry->getUserId()
                )
            );
        }

        $mailer->sendMails($mails);
    }


    public function getProvidedValues() : array
    {
        $returns = array();
        $access = $this->getAccessHelper();
        if ($access->maySeeCockpitItem()) {
            require_once('class.ilObjBookingAcknowledgeGUI.php');
            $this->g_ctrl->setParameterByClass("ilObjBookingAcknowledgeGUI", "ref_id", $this->getRefId());
            $link = $this->g_ctrl->getLinkTargetByClass(
                array("ilObjPluginDispatchGUI", "ilObjBookingAcknowledgeGUI"),
                AcknowledgmentGUI::CMD_SHOW_UPCOMING
            );

            $returns[] = [
                "title" => $this->getTitle(),
                "tooltip" => $this->getDescription(),
                "link" => $link,
                "icon_path" => $this->getIconPath(true),
                "active_icon_path" => $this->getActiceIconPath(true),
                "identifier" => $this->getRefId()
            ];
        }
        return $returns;
    }
}
