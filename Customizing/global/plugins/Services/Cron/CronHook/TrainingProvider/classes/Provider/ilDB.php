<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace CaT\Plugins\TrainingProvider\Provider;

/**
 * Provider data base handle
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ilDB implements DB
{
    const TABLE_NAME = "tp_provider";
    const TABLE_TAGS = "tp_tags";
    const TABLE_TAGS_ALLOCATION = "tp_tags_provider";
    const TABLE_TRAINER = "tp_trainer";

    const NEW_LINE_DELIMITER = "#nl#";
    const TAG_DELIMITER = "#:#";
    const TAGS_DELIMITER = "#|#";

    /**
     * @var /*ilDBPdoMySQLInnoDB
     */
    protected $db = null;

    public function __construct(/*ilDBPdoMySQLInnoDB*/ $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        $this->createTable();
        $this->createSequence();
    }

    /**
     * @inheritdoc
     */
    public function create($name, $rating = 0.0, $info = "", $address1 = "", $country = "", $address2 = "", $postcode = "", $city = "", $homepage = "", $internal_contact = "", $contact = "", $phone = "", $fax = "", $email = "", $general_agreement = false, $terms = "", $valuta = "", $tags = array())
    {
        $next_id = $this->getNextId();
        $provider = new Provider($next_id, $name, $rating, $info, $address1, $country, $address2, $postcode, $city, $homepage, $internal_contact, $contact, $phone, $fax, $email, $general_agreement, $terms, $valuta, array(), $tags);

        $values = array("id" => array("integer", $provider->getId())
                      , "name" => array("text", $provider->getName())
                      , "rating" => array("float", $provider->getRating())
                      , "info" => array("text", $provider->getInfo())
                      , "address1" => array("text", $provider->getAddress1())
                      , "country" => array("text", $provider->getCountry())
                      , "address2" => array("text", $provider->getAddress2())
                      , "postcode" => array("text", $provider->getPostcode())
                      , "city" => array("text", $provider->getCity())
                      , "homepage" => array("text", $provider->getHomepage())
                      , "internal_contact" => array("text", $provider->getInternalContact())
                      , "contact" => array("text", $provider->getContact())
                      , "phone" => array("text", $provider->getPhone())
                      , "fax" => array("text", $provider->getFax())
                      , "email" => array("text", $provider->getEmail())
                      , "general_agreement" => array("integer", $provider->getGeneralAgreement())
                      , "terms" => array("text", $provider->getTerms())
                      , "valuta" => array("text", $provider->getValuta())
                    );

        $this->getDB()->insert(self::TABLE_NAME, $values);

        return $provider;
    }

    /**
     * @inheritdoc
     */
    public function select($id)
    {
        $query = "SELECT prov.name, prov.rating, prov.info, prov.address1, prov.country, prov.address2, prov.postcode, prov.city" . PHP_EOL
                . ", prov.homepage, prov.internal_contact, prov.contact, prov.phone, prov.fax, prov.email, prov.general_agreement, prov.terms, prov.valuta" . PHP_EOL
                . ", GROUP_CONCAT(alloc.id SEPARATOR '" . self::TAGS_DELIMITER . "') as tags" . PHP_EOL
                . "FROM " . self::TABLE_NAME . " prov" . PHP_EOL
                . "LEFT JOIN " . self::TABLE_TAGS_ALLOCATION . " alloc" . PHP_EOL
                . "    ON alloc.provider_id = prov.id" . PHP_EOL
                . "WHERE prov.id = " . $this->getDB()->quote($id, "integer") . PHP_EOL
                . "GROUP BY prov.id" . PHP_EOL
        ;

        $res = $this->getDB()->query($query);

        if ($this->getDB()->numRows($res) == 0) {
            throw new \Exception("no provider found for id " . $id);
        }

        $row = $this->getDB()->fetchAssoc($res);

        $provider = new Provider(
            $id,
            $row["name"],
            (float) $this->getDefaultOnNull($row["rating"], 0.0),
            $this->getDefaultOnNull($row["info"], ""),
            $this->getDefaultOnNull($row["address1"], ""),
            $this->getDefaultOnNull($row["country"], ""),
            $this->getDefaultOnNull($row["address2"], ""),
            $this->getDefaultOnNull($row["postcode"], ""),
            $this->getDefaultOnNull($row["city"], ""),
            $this->getDefaultOnNull($row["homepage"], ""),
            $this->getDefaultOnNull($row["internal_contact"], ""),
            $this->getDefaultOnNull($row["contact"], ""),
            $this->getDefaultOnNull($row["phone"], ""),
            $this->getDefaultOnNull($row["fax"], ""),
            $this->getDefaultOnNull($row["email"], ""),
            (bool) $this->getDefaultOnNull($row["general_agreement"], ""),
            $this->getDefaultOnNull($row["terms"], ""),
            $this->getDefaultOnNull($row["valuta"], ""),
            array() //trainer
                                ,
            $this->getDefaultOnNull(explode(self::TAGS_DELIMITER, $row["tags"]), array())
            );

        return $provider;
    }

    /**
     * @inheritdoc
     */
    public function update(\CaT\Plugins\TrainingProvider\Provider\Provider $provider)
    {
        $where = array("id" => array("integer", $provider->getId())
                    );

        $values = array("name" => array("text", $provider->getName())
                      , "rating" => array("float", $provider->getRating())
                      , "info" => array("text", $provider->getInfo())
                      , "address1" => array("text", $provider->getAddress1())
                      , "country" => array("text", $provider->getCountry())
                      , "address2" => array("text", $provider->getAddress2())
                      , "postcode" => array("text", $provider->getPostcode())
                      , "city" => array("text", $provider->getCity())
                      , "homepage" => array("text", $provider->getHomepage())
                      , "internal_contact" => array("text", $provider->getInternalContact())
                      , "contact" => array("text", $provider->getContact())
                      , "phone" => array("text", $provider->getPhone())
                      , "fax" => array("text", $provider->getFax())
                      , "email" => array("text", $provider->getEmail())
                      , "general_agreement" => array("text", $provider->getGeneralAgreement())
                      , "terms" => array("text", $provider->getTerms())
                      , "valuta" => array("text", $provider->getValuta())
                    );

        $this->getDB()->update(self::TABLE_NAME, $values, $where);
    }

    /**
     * @inheritdoc
     */
    public function delete($id)
    {
        $query = "DELETE FROM " . self::TABLE_NAME . "\n"
                . " WHERE id = " . $this->getDB()->quote($id, "integer");

        $this->getDB()->manipulate($query);
    }

    /**
     * Check the name of the new provider is existing
     *
     * @param string 	$new_provider_name
     *
     * @return bool
     */
    public function providerNameExist(string $new_provider_name)
    {
        $query = "SELECT count(name) AS name" . PHP_EOL
                . "FROM " . self::TABLE_NAME . PHP_EOL
                . "WHERE name = " . $this->getDB()->quote($new_provider_name, "text") . PHP_EOL
        ;

        $res = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($res);

        return $row["name"] != 0;
    }

    /**
     * Get data for provider table
     *
     * @param int[] | [] 	$filtered_tags
     *
     * @return string[]
     */
    public function getProviderOverviewData(array $filtered_tags = array())
    {
        $where = "";
        if ($filtered_tags && count($filtered_tags) > 0) {
            $where = " WHERE " . $this->getDB()->in("tags.id", $filtered_tags, false, "integer");
        }

        $query = "SELECT prov.id, prov.name, prov.rating, prov.info" . PHP_EOL
                . "    , CONCAT_WS('" . self::NEW_LINE_DELIMITER . "', prov.address1, prov.address2, prov.country, prov.postcode, prov.city) AS address" . PHP_EOL
                . "    , prov.homepage, prov.internal_contact" . PHP_EOL
                . "    , CONCAT_WS('" . self::NEW_LINE_DELIMITER . "', prov.contact, prov.phone, prov.fax, prov.email) AS contact" . PHP_EOL
                . "    , 'tags' AS tags" . PHP_EOL
                . "    , prov.general_agreement, prov.terms, prov.valuta" . PHP_EOL
                . "    , GROUP_CONCAT(DISTINCT CONCAT_WS(' ', train.salutation, train.title, train.firstname, CONCAT_WS(', ', train.lastname, train.firstname)) SEPARATOR '" . self::NEW_LINE_DELIMITER . "') as trainer" . PHP_EOL
                . "    , MIN(train.fee) AS min_fee, MAX(train.fee) AS max_fee" . PHP_EOL
                . "    , GROUP_CONCAT(DISTINCT CONCAT_WS('" . self::TAG_DELIMITER . "', tags.name, tags.color) SEPARATOR '" . self::TAGS_DELIMITER . "') as tags" . PHP_EOL
                . "FROM " . self::TABLE_NAME . " prov" . PHP_EOL
                . "LEFT JOIN " . self::TABLE_TRAINER . " train" . PHP_EOL
                . "    ON prov.id = train.provider_id" . PHP_EOL
                . "LEFT JOIN " . self::TABLE_TAGS_ALLOCATION . " talloc" . PHP_EOL
                . "    ON prov.id = talloc.provider_id" . PHP_EOL
                . "LEFT JOIN " . self::TABLE_TAGS . " tags" . PHP_EOL
                . "    ON talloc.id = tags.id" . PHP_EOL
                . $where . PHP_EOL
                . "GROUP BY prov.id, train.provider_id" . PHP_EOL
                ;

        $res = $this->getDB()->query($query);

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $row["address"] = str_replace(self::NEW_LINE_DELIMITER, "<br />", $row["address"]);
            $row["contact"] = str_replace(self::NEW_LINE_DELIMITER, "<br />", $row["contact"]);
            $row["trainer"] = str_replace(self::NEW_LINE_DELIMITER, "<br />", trim($row["trainer"]));

            if ($row["tags"] !== "") {
                $row["tags"] = explode(self::TAGS_DELIMITER, $row["tags"]);
                foreach ($row["tags"] as $key => $tag) {
                    $row["tags"][$key] = explode(self::TAG_DELIMITER, $tag);
                }
            } else {
                $row["tags"] = array();
            }

            $ret[] = $row;
        }

        return $ret;
    }

    /**
     * Checks if the new provider name already exists
     *
     * @param string 	$name
     *
     * @return boolean
     */
    public function nameExists($name)
    {
        $query =
             "SELECT COUNT(name) AS cnt" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "WHERE name = " . $this->getDB()->quote($name, "text") . PHP_EOL
        ;

        $res = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($res);

        if ($row["cnt"] > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get the current name of provider
     *
     * @param int 	$id
     *
     * @return string
     */
    public function getCurrentProviderName(int $id) : string
    {
        $query =
             "SELECT name" . PHP_EOL
            . "FROM " . self::TABLE_NAME . "" . PHP_EOL
            . "WHERE id = " . $this->getDB()->quote($id, "integer") . PHP_EOL
        ;

        $res = $this->getDB()->query($query);
        $row = $this->getDB()->fetchAssoc($res);

        return $row["name"];
    }

    /**
     * Get provider options
     *
     * @return array<int, mixed>
     */
    public function getProviderOptions()
    {
        $query =
             "SELECT id, name" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
            . "ORDER BY name ASC" . PHP_EOL
        ;

        $res = $this->getDB()->query($query);
        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[$row["id"]] = $row["name"];
        }

        return $ret;
    }

    /**
     * Checks the db value is null
     *
     * @param string|int|float|null 	$value
     * @param string|int|float 			$default
     *
     * @return string|int|float
     */
    protected function getDefaultOnNull($value, $default)
    {
        if ($value === null) {
            return $default;
        }

        return $value;
    }

    /**
     * Get all available provider
     *
     * @param string 	$order_column
     * @param string 	$order_direction
     *
     * @return Provider[]
     */
    public function getAllProviders($order_column, $order_direction)
    {
        $query =
            "SELECT id,name,rating,info,address1,country,address2," . PHP_EOL
            . "postcode,city,homepage,internal_contact,contact," . PHP_EOL
            . "phone,fax,email,general_agreement,terms,valuta" . PHP_EOL
            . "FROM " . self::TABLE_NAME . PHP_EOL
        ;

        if ($order_column) {
            $query .= "ORDER BY " . $order_column . " " . $order_direction . PHP_EOL;
        }
        $res = $this->getDB()->query($query);

        $ret = array();
        while ($row = $this->getDB()->fetchAssoc($res)) {
            $ret[] = new Provider(
                (int) $row["id"],
                $row["name"],
                (float) $this->getDefaultOnNull($row["rating"], 0.0),
                $this->getDefaultOnNull($row["info"], ""),
                $this->getDefaultOnNull($row["address1"], ""),
                $this->getDefaultOnNull($row["country"], ""),
                $this->getDefaultOnNull($row["address2"], ""),
                $this->getDefaultOnNull($row["postcode"], ""),
                $this->getDefaultOnNull($row["city"], ""),
                $this->getDefaultOnNull($row["homepage"], ""),
                $this->getDefaultOnNull($row["internal_contact"], ""),
                $this->getDefaultOnNull($row["contact"], ""),
                $this->getDefaultOnNull($row["phone"], ""),
                $this->getDefaultOnNull($row["fax"], ""),
                $this->getDefaultOnNull($row["email"], ""),
                (bool) $this->getDefaultOnNull($row["general_agreement"], ""),
                $this->getDefaultOnNull($row["terms"], ""),
                $this->getDefaultOnNull($row["valuta"], ""),
                array() //trainer
                                    ,
                $this->getDefaultOnNull(explode(self::TAGS_DELIMITER, $row["tags"]), array())
                );
        }
        return $ret;
    }

    /**
     * Creates the provider table
     */
    protected function createTable()
    {
        if (!$this->getDB()->tableExists(self::TABLE_NAME)) {
            $fields = array(
                    "id" => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => true
                    ),
                    "name" => array(
                        'type' => 'text',
                        'length' => 64,
                        'notnull' => true
                    ),
                    "rating" => array(
                        'type' => 'float',
                        'notnull' => false
                    ),
                    "info" => array(
                        'type' => 'clob',
                        'notnull' => false
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
                    ),
                    "homepage" => array(
                        'type' => 'text',
                        'length' => 64,
                        'notnull' => false
                    ),
                    "internal_contact" => array(
                        'type' => 'text',
                        'length' => 64,
                        'notnull' => false
                    ),
                    "contact" => array(
                        'type' => 'text',
                        'length' => 64,
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
                        'length' => 32,
                        'notnull' => false
                    ),
                    "general_agreement" => array(
                        'type' => 'integer',
                        'length' => 1,
                        'notnull' => false
                    ),
                    "terms" => array(
                        'type' => 'clob',
                        'notnull' => false
                    ),
                    "valuta" => array(
                        'type' => 'text',
                        'length' => 32,
                        'notnull' => false
                    ),
                );


            $this->getDB()->createTable(self::TABLE_NAME, $fields);
            $this->getDB()->addPrimaryKey(self::TABLE_NAME, array("id"));
        }
    }

    /**
     * Update columns of table
     *
     * @return null
     */
    public function updateTable1()
    {
        $attributes = array('type' => 'text',
                             'length' => 128,
                             'notnull' => false
                );
        $this->getDB()->modifyTableColumn(self::TABLE_NAME, "email", $attributes);
    }

    /**
     * Update columns of table
     *
     * @return null
     */
    public function updateTable2()
    {
        $attributes = array('type' => 'text',
                        'length' => 10,
                        'default' => "",
                        'notnull' => true
                    );
        $this->getDB()->modifyTableColumn(self::TABLE_NAME, "postcode", $attributes);

        $attributes = array('type' => 'text',
                        'length' => 64,
                        'default' => "",
                        'notnull' => true
                    );
        $this->getDB()->modifyTableColumn(self::TABLE_NAME, "city", $attributes);
    }

    /**
     * Next db update step
     *
     * @return void
     */
    public function update3()
    {
        if ($this->getDB()->tableColumnExists(self::TABLE_NAME, "homepage")) {
            $field = array(
                'type' => 'text',
                'length' => 255,
                'notnull' => false
            );

            $this->getDB()->modifyTableColumn(self::TABLE_NAME, "homepage", $field);
        }
    }

    /**
     * Creates the sequence for provider obj ids
     */
    protected function createSequence()
    {
        if (!$this->getDB()->sequenceExists(self::TABLE_NAME)) {
            $this->getDB()->createSequence(self::TABLE_NAME);
        }
    }

    /**
     * Get the DB handler
     *
     * @return \ilDB
     */
    protected function getDB()
    {
        if ($this->db === null) {
            throw new Exception("no db handler");
        }

        return $this->db;
    }

    /**
     * Get the next id for new provider
     *
     * @return int
     */
    protected function getNextId()
    {
        return (int) $this->getDB()->nextId(self::TABLE_NAME);
    }
}
