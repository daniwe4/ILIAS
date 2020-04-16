<?php declare(strict_types=1);

namespace CaT\Plugins\OrguByMailDomain\Configuration;

class Repository
{
    protected $db;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    const TABLE_CONFIG = 'x_obmd_config';
    const COLUMN_ID = 'id';
    const COLUMN_TITLE = 'title';
    const COLUMN_PROSITION = 'position';
    const COLUMN_DESCRIPTION = 'description';

    const TABLE_CONFIG_ORGUS = 'x_obmd_conf_orgu';
    const COLUMN_ORGU = 'orgu';

    public function loadAll() : array
    {
        $q = 'SELECT'
            . '	' . self::COLUMN_ID
            . '	,' . self::COLUMN_ORGU
            . '	FROM ' . self::TABLE_CONFIG_ORGUS;
        $res = $this->db->query($q);
        $orgus_aux = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $id = (int) $rec[self::COLUMN_ID];
            if (!array_key_exists($id, $orgus_aux)) {
                $orgus_aux[$id] = [];
            }
            $orgus_aux[$id][] = (int) $rec[self::COLUMN_ORGU];
        }
        $q = 'SELECT'
            . '	' . self::COLUMN_ID
            . '	,' . self::COLUMN_TITLE
            . '	,' . self::COLUMN_PROSITION
            . '	,' . self::COLUMN_DESCRIPTION
            . '	FROM ' . self::TABLE_CONFIG;
        $res = $this->db->query($q);
        $return = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $id = (int) $rec[self::COLUMN_ID];
            $return[] = new Configuration(
                $id,
                $rec[self::COLUMN_TITLE],
                $orgus_aux[$id],
                (int) $rec[self::COLUMN_PROSITION],
                $rec[self::COLUMN_DESCRIPTION]
                        );
        }
        return $return;
    }

    public function loadById(int $id)
    {
        $q = 'SELECT'
            . '	' . self::COLUMN_ID
            . '	,' . self::COLUMN_ORGU
            . '	FROM ' . self::TABLE_CONFIG_ORGUS
            . '	WHERE ' . self::COLUMN_ID . ' = ' . $this->db->quote($id, 'integer');
        ;
        $res = $this->db->query($q);
        $orgus_aux = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $orgus_aux[] = (int) $rec[self::COLUMN_ORGU];
        }
        $q = 'SELECT'
            . '	' . self::COLUMN_ID
            . '	,' . self::COLUMN_TITLE
            . '	,' . self::COLUMN_PROSITION
            . '	,' . self::COLUMN_DESCRIPTION
            . '	FROM ' . self::TABLE_CONFIG
            . '	WHERE ' . self::COLUMN_ID . ' = ' . $this->db->quote($id, 'integer');
        $res = $this->db->query($q);
        while ($rec = $this->db->fetchAssoc($res)) {
            $id = (int) $rec[self::COLUMN_ID];
            return new Configuration(
                $id,
                $rec[self::COLUMN_TITLE],
                $orgus_aux,
                (int) $rec[self::COLUMN_PROSITION],
                $rec[self::COLUMN_DESCRIPTION]
                        );
        }
        return null;
    }

    protected function idByTitle(string $title) : int
    {
        $q = 'SELECT'
            . '	' . self::COLUMN_ID
            . '	FROM ' . self::TABLE_CONFIG
            . '	WHERE ' . self::COLUMN_TITLE . ' = ' . $this->db->quote($title, 'string');
        $res = $this->db->query($q);
        while ($rec = $this->db->fetchAssoc($res)) {
            return $id = (int) $rec[self::COLUMN_ID];
        }
        return -1;
    }

    public function loadByTitle(string $title)
    {
        $id = $this->idByTitle($this->canonicTitle($title));
        if ($id === -1) {
            return null;
        }
        return $this->loadById($id);
    }

    public function create(
        string $title,
        array $orgus,
        int $position,
        string $description
    ) : Configuration {
        $id = $this->nextId();
        $title = $this->canonicTitle($title);
        $this->storeData(
            $id,
            $title,
            $position,
            $description
        );
        $this->storeOrgus($id, $orgus);
        return new Configuration($id, $title, $orgus, $position, $description);
    }



    protected function storeData(
        int $id,
        string $title,
        int $position,
        string $description
    ) {
        $this->db->insert(
            self::TABLE_CONFIG,
            [
                self::COLUMN_TITLE => ['text',$title],
                self::COLUMN_PROSITION => ['integer',$position],
                self::COLUMN_DESCRIPTION => ['text',$description],
                self::COLUMN_ID => ['integer',$id]
            ]
        );
    }


    protected function storeOrgus(
        int $id,
        array $orgus
    ) {
        foreach ($orgus as $orgu_id) {
            $this->db->insert(
                self::TABLE_CONFIG_ORGUS,
                [
                    self::COLUMN_ORGU => ['integer',$orgu_id],
                    self::COLUMN_ID => ['integer',$id]
                ]
            );
        }
    }

    public function delete(Configuration $config)
    {
        $id = $config->getId();
        $this->deleteData($id);
        $this->deleteOrgus($id);
    }

    protected function deleteData(int $id)
    {
        $this->db->manipulate(
            'DELETE FROM'
            . '	' . self::TABLE_CONFIG
            . '	WHERE ' . self::COLUMN_ID . ' = ' . $this->db->quote($id, 'integer')
        );
    }

    protected function deleteOrgus(int $id)
    {
        $this->db->manipulate(
            'DELETE FROM'
            . '	' . self::TABLE_CONFIG_ORGUS
            . '	WHERE ' . self::COLUMN_ID . ' = ' . $this->db->quote($id, 'integer')
        );
    }

    public function update(Configuration $config)
    {
        $id = $config->getId();
        $this->updateData(
            $id,
            $this->canonicTitle($config->getTitle()),
            $config->getPosition(),
            $config->getDescription()
        );
        $this->updateOrgus(
            $id,
            $config->getOrguIds()
        );
    }

    protected function canonicTitle(string $title) : string
    {
        return '*@' . strtolower(ltrim(trim($title), '@*'));
    }

    protected function updateData(
        int $id,
        string $title,
        int $position,
        string $description
    ) {
        $this->db->update(
            self::TABLE_CONFIG,
            [
                self::COLUMN_TITLE => ['text',$title],
                self::COLUMN_PROSITION => ['integer',$position],
                self::COLUMN_DESCRIPTION => ['text',$description]
            ],
            [
                self::COLUMN_ID => ['integer',$id]
            ]
        );
    }

    protected function updateOrgus(
        int $id,
        array $orgus
    ) {
        $this->deleteOrgus($id, $orgus);
        $this->storeOrgus($id, $orgus);
    }

    protected function nextId() : int
    {
        return (int) $this->db->nextId(self::TABLE_CONFIG);
    }
}
