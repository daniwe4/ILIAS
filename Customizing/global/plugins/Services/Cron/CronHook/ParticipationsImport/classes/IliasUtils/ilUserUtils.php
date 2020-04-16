<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\IliasUtils;

class ilUserUtils implements UserUtils
{
    const USR_DATA_TABLE = 'usr_data';
    const UDF_DATA_TABLE = 'udf_text';
    const UDF_DEFINITION_TABLE = 'udf_definition';


    protected $db;
    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }
    public function userIdByLogin(string $login) : int
    {
        $res = $this->db->query(
            'SELECT usr_id'
            . '	FROM ' . self::USR_DATA_TABLE
            . '	WHERE login = ' . $this->db->quote($login, 'text')
        );
        while ($rec = $this->db->fetchAssoc($res)) {
            return (int) $rec['usr_id'];
        }
        return self::NONE;
    }
    public function userIdsByEmail(string $email) : array
    {
        $res = $this->db->query(
            'SELECT usr_id'
            . '	FROM ' . USR_DATA_TABLE
            . '	WHERE email = ' . $this->db->quote($email, 'text')
        );
        while ($rec = $this->db->fetchAssoc($res)) {
            return (int) $rec['usr_id'];
        }
        return self::NONE;
    }
    public function userIdByUdfField(string $value, int $field_id) : array
    {
        $res = $this->db->query(
            'SELECT usr_id'
            . '	FROM ' . self::UDF_DATA_TABLE
            . '	WHERE value = ' . $this->db->quote($value, 'text')
            . '		AND field_id = ' . $this->db->quote($field_id, 'integer')
        );
        $return = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $return[] = (int) $rec['usr_id'];
        }
        return $return;
    }
    /**
     * field_id => title
     */
    public function udfFields() : array
    {
        $res = $this->db->query(
            'SELECT field_id'
            . '	,field_name'
            . '	FROM ' . self::UDF_DEFINITION_TABLE
        );
        $return = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $return[(int) $rec['field_id']] = (string) $rec['field_name'];
        }
        return $return;
    }
}
