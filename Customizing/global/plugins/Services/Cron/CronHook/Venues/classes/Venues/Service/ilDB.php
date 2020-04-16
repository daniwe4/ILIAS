<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Service;

use \CaT\Plugins\Venues\ObjectFactory;

/**
 * Implementation of for service db interface
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    use ObjectFactory;

    const TABLE_NAME = "venues_service";

    /**
     * @var \ilDBInterface
     */
    protected $db = null;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function create(
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
    ) : Service {
        $service = $this->getServiceObject(
            $id,
            $mail_service_list,
            $mail_room_setup,
            $days_send_service,
            $days_send_room_setup,
            $mail_material_list,
            $days_send_material_list,
            $mail_accomodation_list,
            $days_send_accomodation_list,
            $days_remind_accomodation_list
        );

        $values = array("id" => array("integer", $service->getId()),
            "mail_service_list" => array("text", $service->getMailServiceList()),
            "mail_room_setup" => array("text", $service->getMailRoomSetup()),
            "days_send_service" => array("integer", $service->getDaysSendService()),
            "days_send_room_setup" => array("integer", $service->getDaysSendRoomSetup()),
            "mail_material_list" => array("text", $service->getMailMaterialList()),
            "days_send_material_list" => array("integer", $service->getDaysSendMaterial()),
            "mail_accomodation_list" => array("text", $service->getMailAccomodationList()),
            "days_send_accomodation_list" => array("integer", $service->getDaysSendAccomodation()),
            "days_remind_acco_list" => array("integer", $service->getDaysRemindAccomodation())
        );

        $this->db->insert(self::TABLE_NAME, $values);

        return $service;
    }

    /**
     * @inheritdoc
     */
    public function update(Service $service)
    {
        $where = array("id" => array("integer", $service->getId()));

        $values = array("mail_service_list" => array("text", $service->getMailServiceList()),
            "mail_room_setup" => array("text", $service->getMailRoomSetup()),
            "days_send_service" => array("integer", $service->getDaysSendService()),
            "days_send_room_setup" => array("integer", $service->getDaysSendRoomSetup()),
            "mail_material_list" => array("text", $service->getMailMaterialList()),
            "days_send_material_list" => array("integer", $service->getDaysSendMaterial()),
            "mail_accomodation_list" => array("text", $service->getMailAccomodationList()),
            "days_send_accomodation_list" => array("integer", $service->getDaysSendAccomodation()),
            "days_remind_acco_list" => array("integer", $service->getDaysRemindAccomodation())
        );

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function delete(int $id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->db->quote($id, "integer");

        $this->db->manipulate($query);
    }

    /**
     * Create the table for rating configuration
     *
     * @return void
     */
    public function createTable()
    {
        if (!$this->db->tableExists(self::TABLE_NAME)) {
            $fields = array(
                    "id" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    "mail_service_list" => array(
                        'type' => 'text',
                        'length' => 256,
                        'notnull' => false
                    ),
                    "mail_room_setup" => array(
                        'type' => 'text',
                        'length' => 256,
                        'notnull' => false
                    ),
                    "days_send_service" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    ),
                    "days_send_room_setup" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    )
                );

            $this->db->createTable(self::TABLE_NAME, $fields);
        }
    }

    /**
     * Create the primary key for table
     *
     * @return void
     */
    public function createPrimary()
    {
        $this->db->addPrimaryKey(self::TABLE_NAME, array("id"));
    }

    /**
     * Update step 1
     *
     * @return void
     */
    public function update1()
    {
        if (!$this->db->tableColumnExists(self::TABLE_NAME, "mail_material_list")) {
            $field = array(
                'type' => 'text',
                'length' => 256,
                'notnull' => false
            );

            $this->db->addTableColumn(self::TABLE_NAME, "mail_material_list", $field);
        }

        if (!$this->db->tableColumnExists(self::TABLE_NAME, "days_send_material_list")) {
            $field = array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => false
            );

            $this->db->addTableColumn(self::TABLE_NAME, "days_send_material_list", $field);
        }
    }

    /**
     * Update step 2
     *
     * @return void
     */
    public function update2()
    {
        if (!$this->db->tableColumnExists(self::TABLE_NAME, "mail_accomodation_list")) {
            $field = array(
                'type' => 'text',
                'length' => 256,
                'notnull' => false
            );

            $this->db->addTableColumn(self::TABLE_NAME, "mail_accomodation_list", $field);
        }

        if (!$this->db->tableColumnExists(self::TABLE_NAME, "days_send_accomodation_list")) {
            $field = array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => false
            );

            $this->db->addTableColumn(self::TABLE_NAME, "days_send_accomodation_list", $field);
        }
    }

    /**
     * Update step 3
     *
     * @return void
     */
    public function update3()
    {
        if (!$this->db->tableColumnExists(self::TABLE_NAME, "days_remind_acco_list")) {
            $field = array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => false
            );

            $this->db->addTableColumn(self::TABLE_NAME, "days_remind_acco_list", $field);
        }
    }
}
