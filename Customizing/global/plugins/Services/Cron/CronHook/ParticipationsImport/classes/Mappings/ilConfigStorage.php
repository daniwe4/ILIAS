<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\Mappings;

class ilConfigStorage implements ConfigStorage
{
    const KEYWORD_PREFIX = 'part_imp_mappings_';
    const KEYWORD_EXTERN_USR_ID_COL_TITLE = self::KEYWORD_PREFIX . 'extern_usr_id_col_title';

    const TABLE_MAPPINGS = 'part_imp_bk_ps_maps';
    const FIELD_KEY = 'key';
    const FIELD_VAL = 'val';
    const FIELD_TYPE = 'type';

    const TYPE_BOOKING_STATUS = 'booking';
    const TYPE_PARTICIPAION_STATUS = 'participation';

    public function __construct(
        \ilSetting $set,
        \ilDBInterface $db
    ) {
        $this->set = $set;
        $this->db = $db;
    }
    public function loadCurrentConfig() : Config
    {
        return new Config((int) $this->set->get(self::KEYWORD_EXTERN_USR_ID_COL_TITLE));
    }
    public function storeConfigAsCurrent(Config $config)
    {
        $this->set->set(self::KEYWORD_EXTERN_USR_ID_COL_TITLE, $config->externUsrIdField());
    }
    public function loadParticipationStatusMapping() : ParticipationStatusMapping
    {
        $ret = new ParticipationStatusRelationMapping();
        foreach ($this->loadMappinFor(self::TYPE_PARTICIPAION_STATUS) as $key => $value) {
            $ret->addRelation((string) $key, (string) $value);
        }
        return $ret;
    }
    public function storeParticipationStatusMapping(ParticipationStatusMapping $psm)
    {
        $this->clearByType(self::TYPE_PARTICIPAION_STATUS);
        foreach ($psm as $extern => $intern) {
            $this->insertByType((string) $extern, (string) $intern, self::TYPE_PARTICIPAION_STATUS);
        }
    }
    public function loadBookingStatusMapping() : BookingStatusMapping
    {
        $ret = new BookingStatusRelationMapping();
        foreach ($this->loadMappinFor(self::TYPE_BOOKING_STATUS) as $key => $value) {
            $ret->addRelation((string) $key, (string) $value);
        }
        return $ret;
    }
    public function storeBookingStatusMapping(BookingStatusMapping $bsm)
    {
        $this->clearByType(self::TYPE_BOOKING_STATUS);
        foreach ($bsm as $extern => $intern) {
            $this->insertByType((string) $extern, (string) $intern, self::TYPE_BOOKING_STATUS);
        }
    }

    protected function insertByType(string $key, string $value, string $type)
    {
        $this->db->insert(
            self::TABLE_MAPPINGS,
            [
                self::FIELD_KEY => ['text',$key],
                self::FIELD_VAL => ['text',$value],
                self::FIELD_TYPE => ['text',$type]
            ]
        );
    }

    protected function clearByType(string $type)
    {
        $this->db->manipulate(
            'DELETE FROM ' . self::TABLE_MAPPINGS
            . '	WHERE ' . self::FIELD_TYPE . ' = ' . $this->db->quote($type, 'text')
        );
    }

    protected function loadMappinFor(string $type)
    {
        $res = $this->db->query(
            'SELECT'
            . '	' . self::FIELD_VAL
            . '	,`' . self::FIELD_KEY . '`'
            . '	FROM ' . self::TABLE_MAPPINGS
            . '	WHERE ' . self::FIELD_TYPE . ' = ' . $this->db->quote($type, 'text')
        );
        $return = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $return[$rec[self::FIELD_KEY]] = $rec[self::FIELD_VAL];
        }
        return $return;
    }
}
