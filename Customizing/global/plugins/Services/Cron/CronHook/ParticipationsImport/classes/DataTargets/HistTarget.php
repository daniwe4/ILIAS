<?php declare(strict_types=1);

namespace CaT\Plugins\ParticipationsImport\DataTargets;

abstract class HistTarget
{
    protected $db;
    protected $log;

    const FIELD_CREATOR_ID = 'creator';
    const FIELD_CREATED_TS = 'created_ts';

    public function __construct(
        \ilDBInterface $db
    ) {
        $this->db = $db;
        $this->log = $log;
    }


    const FIELD_ROW_ID = 'row_id';
    const CREATOR_ID = -999;

    protected function updateHHDTable(array $data, array $pk)
    {
        $this->db->replace($this->headTable(), $pk, $data);
    }

    protected function updateHSTTable(array $data)
    {
        $data[self::FIELD_ROW_ID] = ['integer',(int) $this->db->nextId($this->histTable())];
        $this->db->insert($this->histTable(), $data);
    }

    protected function updateData(array $data)
    {
        $data = $this->addGenericInfosToData($data);
        $this->updateHHDTable(
            $data,
            $this->getPkFromData($data)
        );
        $this->updateHSTTable($data);
    }

    protected function addGenericInfosToData(array $data)
    {
        $data[self::FIELD_CREATOR_ID] = ['integer',self::CREATOR_ID];
        $data[self::FIELD_CREATED_TS] = ['integer',time()];
        return $data;
    }

    abstract protected function histTable() : string;
    abstract protected function headTable() : string;
    abstract protected function getPkFromData(array $data) : array;
}
