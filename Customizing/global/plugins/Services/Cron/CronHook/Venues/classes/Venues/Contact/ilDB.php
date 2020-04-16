<?php

declare(strict_types=1);

namespace CaT\Plugins\Venues\Venues\Contact;

use \CaT\Plugins\Venues\ObjectFactory;

/**
 * Implementation of DB inteface for contact configuration
 *
 * @author Stefan Hecken 	<stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    use ObjectFactory;

    const TABLE_NAME = "venues_contact";

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
        string $internal_contact = "",
        string $contact = "",
        string $phone = "",
        string $fax = "",
        string $email = ""
    ) : Contact {
        $contact = new Contact($id, $internal_contact, $contact, $phone, $fax, $email);

        $values = array("id" => array("integer", $contact->getId()),
                "internal_contact" => array("text", $contact->getInternalContact()),
                "contact" => array("text", $contact->getContact()),
                "phone" => array("text", $contact->getPhone()),
                "fax" => array("text", $contact->getFax()),
                "email" => array("text", $contact->getEmail())
            );

        $this->db->insert(self::TABLE_NAME, $values);

        return $contact;
    }

    /**
     * @inheritdoc
     */
    public function update(Contact $contact)
    {
        $where = array("id" => array("integer", $contact->getId()));

        $values = array("internal_contact" => array("text", $contact->getInternalContact()),
                "contact" => array("text", $contact->getContact()),
                "phone" => array("text", $contact->getPhone()),
                "fax" => array("text", $contact->getFax()),
                "email" => array("text", $contact->getEmail())
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
     * Create the table for contact configuration
     *
     * @return null
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
                    "internal_contact" => array(
                        'type' => 'text',
                        'length' => 128,
                        'notnull' => false
                    ),
                    "contact" => array(
                        'type' => 'text',
                        'length' => 128,
                        'notnull' => false
                    ),
                    "phone" => array(
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => false
                    ),
                    "fax" => array(
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => false
                    ),
                    "email" => array(
                        'type' => 'text',
                        'length' => 128,
                        'notnull' => false
                    )
                );


            $this->db->createTable(self::TABLE_NAME, $fields);
        }
    }

    /**
     * Create the primary key for table
     *
     * @return null
     */
    public function createPrimary()
    {
        $this->db->addPrimaryKey(self::TABLE_NAME, array("id"));
    }
}
