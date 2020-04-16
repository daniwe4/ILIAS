<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Service;

use CaT\Plugins\Venues\Venues\ConfigFormHelper;
use CaT\Plugins\Venues\Venues\Venue;
use CaT\Plugins\Venues\ilActions;

require_once("Services/Form/classes/class.ilPropertyFormGUI.php");

/**
 * Assistant class for venu edit gui
 *
 * @author Stefan Hecken 	<stefan.heclen@concepts-and-training.de>
 */
class FormHelper implements ConfigFormHelper
{
    const F_MAIL_SERVICE = "f_mail_service";
    const F_MAIL_ROOM_SETUP = "f_mail_room_setup";
    const F_DAYS_SEND_SERVICE_LIST = "f_days_send_service_list";
    const F_DAYS_SEND_ROOM_SETUP = "f_days_send_room_setup";
    const F_MAIL_MATERIAL_SETUP = "f_mail_material_list";
    const F_DAYS_SEND_MATERIAL_LIST = "f_days_send_material_list";
    const F_MAIL_ACCOMODATION_SETUP = "f_mail_accomodation_list";
    const F_DAYS_SEND_ACCOMODATION_LIST = "f_days_send_accomodation_list";
    const F_DAYS_REMIND_ACCOMODATION_LIST = "f_days_remind_accomodation_list";

    /**
     * @var ilActions
     */
    protected $actions;

    /**
     * @var Closure
     */
    protected $txt;

    public function __construct(ilActions $actions, \Closure $txt)
    {
        $this->actions = $actions;
        $this->txt = $txt;
    }

    /**
     * @inheritdoc
     */
    public function addFormItems(\ilPropertyFormGUI $form)
    {
        $sh = new \ilFormSectionHeaderGUI();
        $sh->setTitle($this->txt("sh_service"));
        $form->addItem($sh);

        $ti = new \ilEMailInputGUI($this->txt("mail_service"), self::F_MAIL_SERVICE);
        $ti->setInfo($this->txt("mail_service_info"));
        $ti->setSize(40);
        $ti->setMaxLength(256);
        $form->addItem($ti);

        $ni = new \ilNumberInputGUI($this->txt("days_mail_service_list"), self::F_DAYS_SEND_SERVICE_LIST);
        $ni->setInfo($this->txt("days_mail_service_list_info"));
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ti = new \ilEMailInputGUI($this->txt("mail_room_setup"), self::F_MAIL_ROOM_SETUP);
        $ti->setInfo($this->txt("mail_room_setup_info"));
        $ti->setSize(40);
        $ti->setMaxLength(256);
        $form->addItem($ti);

        $ni = new \ilNumberInputGUI($this->txt("days_mail_room_setup"), self::F_DAYS_SEND_ROOM_SETUP);
        $ni->setInfo($this->txt("days_mail_room_setup_info"));
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ti = new \ilEMailInputGUI($this->txt("mail_material_list"), self::F_MAIL_MATERIAL_SETUP);
        $ti->setInfo($this->txt("mail_material_list_info"));
        $ti->setSize(40);
        $ti->setMaxLength(256);
        $form->addItem($ti);

        $ni = new \ilNumberInputGUI($this->txt("days_mail_material_list"), self::F_DAYS_SEND_MATERIAL_LIST);
        $ni->setInfo($this->txt("days_mail_material_list_info"));
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ti = new \ilEMailInputGUI($this->txt("mail_accomodation_list"), self::F_MAIL_ACCOMODATION_SETUP);
        $ti->setInfo($this->txt("mail_accomodation_list_info"));
        $ti->setSize(40);
        $ti->setMaxLength(256);
        $form->addItem($ti);

        $ni = new \ilNumberInputGUI($this->txt("days_mail_accomodation_list"), self::F_DAYS_SEND_ACCOMODATION_LIST);
        $ni->setInfo($this->txt("days_mail_accomodation_list_info"));
        $ni->setMinValue(0, true);
        $form->addItem($ni);

        $ni = new \ilNumberInputGUI($this->txt("days_remind_accomodation_list"), self::F_DAYS_REMIND_ACCOMODATION_LIST);
        $ni->setInfo($this->txt("days_remind_accomodation_list_info"));
        $ni->setMinValue(0, true);
        $form->addItem($ni);
    }

    /**
     * @inheritdoc
     */
    public function createObject(int $venue_id, array $post)
    {
        $days_mail_service_list = $this->emptyToInt($post[self::F_DAYS_SEND_SERVICE_LIST]);
        $days_mail_room_setup = $this->emptyToInt($post[self::F_DAYS_SEND_ROOM_SETUP]);
        $days_mail_material_list = $this->emptyToInt($post[self::F_DAYS_SEND_MATERIAL_LIST]);
        $days_mail_accomodation_list = $this->emptyToInt($post[self::F_DAYS_SEND_ACCOMODATION_LIST]);
        $days_remind_accomodation_list = $this->emptyToInt($post[self::F_DAYS_REMIND_ACCOMODATION_LIST]);

        $this->actions->createServiceObject(
            $venue_id,
            trim($post[self::F_MAIL_SERVICE]),
            trim($post[self::F_MAIL_ROOM_SETUP]),
            $days_mail_service_list,
            $days_mail_room_setup,
            trim($post[self::F_MAIL_MATERIAL_SETUP]),
            $days_mail_material_list,
            trim($post[self::F_MAIL_ACCOMODATION_SETUP]),
            $days_mail_accomodation_list,
            $days_remind_accomodation_list
        );
    }

    /**
     * @inheritdoc
     */
    public function getObject(int $venue_id, array $post)
    {
        $days_mail_service_list = $this->emptyToInt($post[self::F_DAYS_SEND_SERVICE_LIST]);
        $days_mail_room_setup = $this->emptyToInt($post[self::F_DAYS_SEND_ROOM_SETUP]);
        $days_mail_material_list = $this->emptyToInt($post[self::F_DAYS_SEND_MATERIAL_LIST]);
        $days_mail_accomodation_list = $this->emptyToInt($post[self::F_DAYS_SEND_ACCOMODATION_LIST]);
        $days_remind_accomodation_list = $this->emptyToInt($post[self::F_DAYS_REMIND_ACCOMODATION_LIST]);

        return $this->actions->getServiceObject(
            $venue_id,
            trim($post[self::F_MAIL_SERVICE]),
            trim($post[self::F_MAIL_ROOM_SETUP]),
            $days_mail_service_list,
            $days_mail_room_setup,
            trim($post[self::F_MAIL_MATERIAL_SETUP]),
            $days_mail_material_list,
            trim($post[self::F_MAIL_ACCOMODATION_SETUP]),
            $days_mail_accomodation_list,
            $days_remind_accomodation_list
        );
    }

    /**
     * @inheritdoc
     */
    public function addValues(array &$values, Venue $venue)
    {
        $values[self::F_MAIL_SERVICE] = $venue->getService()->getMailServiceList();
        $values[self::F_MAIL_ROOM_SETUP] = $venue->getService()->getMailRoomSetup();
        $values[self::F_DAYS_SEND_SERVICE_LIST] = $venue->getService()->getDaysSendService();
        $values[self::F_DAYS_SEND_ROOM_SETUP] = $venue->getService()->getDaysSendRoomSetup();
        $values[self::F_DAYS_SEND_MATERIAL_LIST] = $venue->getService()->getDaysSendMaterial();
        $values[self::F_MAIL_MATERIAL_SETUP] = $venue->getService()->getMailMaterialList();
        $values[self::F_MAIL_ACCOMODATION_SETUP] = $venue->getService()->getMailAccomodationList();
        $values[self::F_DAYS_SEND_ACCOMODATION_LIST] = $venue->getService()->getDaysSendAccomodation();
        $values[self::F_DAYS_REMIND_ACCOMODATION_LIST] = $venue->getService()->getDaysRemindAccomodation();
    }

    /**
     * @return int | null
     */
    protected function emptyToInt(string $value)
    {
        if ($value == "") {
            return null;
        }

        return (int) $value;
    }

    protected function txt(string $code) : string
    {
        return call_user_func($this->txt, $code);
    }
}
