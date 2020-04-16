<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Service;

/**
 * Venue configuration entries for service settings
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class Service
{
    /**
     * @var int
     */
    protected $id;

    /**
     * Rating venue got at the moment
     *
     * @var string
     */
    protected $mail_room_setup;

    /**
     * Some additional infos
     *
     * @var string
     */
    protected $mail_service_list;

    /**
     * Send list x days before training starts
     *
     * @var int | null
     */
    protected $days_send_service;

    /**
     * Send list x days before training starts
     *
     * @var int | null
     */
    protected $days_send_room_setup;

    /**
     * Recipient for material list
     *
     * @var string
     */
    protected $mail_material_list;

    /**
     * Send list x days before training starts
     *
     * @var int | null
     */
    protected $days_send_material_list;

    /**
     * Recipient for accomodation list
     *
     * @var string
     */
    protected $mail_accomodation_list;

    /**
     * Send list x days before training starts
     *
     * @var int | null
     */
    protected $days_send_accomodation_list;

    /**
     * Send final list x days before training starts
     *
     * @var int | null
     */
    protected $days_remind_accomodation_list;

    public function __construct(
        int $id,
        string $mail_service_list = "",
        string $mail_room_setup = "",
        int $days_send_service = null,
        int $days_send_room_setup = null,
        string $mail_material_list = "",
        int $days_send_material_list = null,
        string $mail_accomodation_list = "",
        int $days_send_accomodation_list = null,
        int $days_remind_accomodation_list = null
    ) {
        $this->id = $id;
        $this->mail_service_list = $mail_service_list;
        $this->mail_room_setup = $mail_room_setup;
        $this->days_send_service = $days_send_service;
        $this->days_send_room_setup = $days_send_room_setup;
        $this->mail_material_list = $mail_material_list;
        $this->days_send_material_list = $days_send_material_list;
        $this->mail_accomodation_list = $mail_accomodation_list;
        $this->days_send_accomodation_list = $days_send_accomodation_list;
        $this->days_remind_accomodation_list = $days_remind_accomodation_list;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getMailServiceList() : string
    {
        return $this->mail_service_list;
    }

    public function getMailRoomSetup() : string
    {
        return $this->mail_room_setup;
    }

    /**
     * @return int | null
     */
    public function getDaysSendService()
    {
        return $this->days_send_service;
    }

    /**
     * @return int | null
     */
    public function getDaysSendRoomSetup()
    {
        return $this->days_send_room_setup;
    }

    public function getMailMaterialList() : string
    {
        return $this->mail_material_list;
    }

    /**
     * @return int | null
     */
    public function getDaysSendMaterial()
    {
        return $this->days_send_material_list;
    }

    public function getMailAccomodationList() : string
    {
        return $this->mail_accomodation_list;
    }

    /**
     * @return int | null
     */
    public function getDaysSendAccomodation()
    {
        return $this->days_send_accomodation_list;
    }
    /**
     * @return int | null
     */
    public function getDaysRemindAccomodation()
    {
        return $this->days_remind_accomodation_list;
    }
}
