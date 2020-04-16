<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Address;

use \CaT\Plugins\Venues\ObjectFactory;

/**
 * Implementation of DB inteface for capacity configuration
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    use ObjectFactory;

    const TABLE_NAME = "venues_address";

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
        string $address1 = "",
        string $country = "",
        string $address2 = "",
        string $postcode = "",
        string $city = "",
        float $latitude = 0.0,
        float $longitude = 0.0,
        int $zoom = 10
    ) : Address {
        $address = new Address($id, $address1, $country, $address2, $postcode, $city, $latitude, $longitude, $zoom);

        $values = array("id" => array("integer", $address->getId()),
                "address1" => array("text", $address->getAddress1()),
                "country" => array("text", $address->getCountry()),
                "address2" => array("text", $address->getAddress2()),
                "postcode" => array("text", $address->getPostcode()),
                "city" => array("text", $address->getCity()),
                "latitude" => array("float", $address->getLatitude()),
                "longitude" => array("float", $address->getLongitude()),
                "zoom" => array("integer", $address->getZoom())
            );

        $this->db->insert(self::TABLE_NAME, $values);

        return $address;
    }

    /**
     * @inheritdoc
     */
    public function update(Address $address)
    {
        $where = array("id" => array("integer", $address->getId()));

        $values = array("address1" => array("text", $address->getAddress1()),
                "country" => array("text", $address->getCountry()),
                "address2" => array("text", $address->getAddress2()),
                "postcode" => array("text", $address->getPostcode()),
                "city" => array("text", $address->getCity()),
                "latitude" => array("float", $address->getLatitude()),
                "longitude" => array("float", $address->getLongitude()),
                "zoom" => array("integer", $address->getZoom())
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
     * Create the table for capacity configuration
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
                    "address1" => array(
                        'type' => 'text',
                        'length' => 64,
                        'notnull' => false
                    ),
                    "country" => array(
                        'type' => 'text',
                        'length' => 64,
                        'notnull' => false
                    ),
                    "address2" => array(
                        'type' => 'text',
                        'length' => 64,
                        'notnull' => false
                    ),
                    "postcode" => array(
                        'type' => 'text',
                        'length' => 10,
                        'notnull' => false
                    ),
                    "city" => array(
                        'type' => 'text',
                        'length' => 64,
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
     * Update 1 for db
     *
     * @return void
     */
    public function update1()
    {
        if (!$this->db->tableColumnExists(self::TABLE_NAME, "latitude")) {
            $fields = array(
                'type' => 'float',
                'notnull' => true
            );

            $this->db->addTableColumn(self::TABLE_NAME, "latitude", $fields);
        }

        if (!$this->db->tableColumnExists(self::TABLE_NAME, "longitude")) {
            $fields = array(
                'type' => 'float',
                'notnull' => true
            );

            $this->db->addTableColumn(self::TABLE_NAME, "longitude", $fields);
        }

        if (!$this->db->tableColumnExists(self::TABLE_NAME, "zoom")) {
            $fields = array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            );

            $this->db->addTableColumn(self::TABLE_NAME, "zoom", $fields);
        }
    }
}
